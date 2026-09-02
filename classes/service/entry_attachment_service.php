<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_monlaututoria\service;

use local_monlaututoria\repository\entry_repository;
use local_monlaututoria\repository\entry_attachment_repository;
use local_monlaututoria\domain\entry_attachment_category;
use local_monlaututoria\domain\entry_status;
use local_monlaututoria\event\entry_attachment_added;

/**
 * Application service for tutoring entry attachments (phase 5.6). The file
 * bytes themselves are entirely Moodle's File API's responsibility — this
 * class only decides which entry a batch of uploaded files belongs to, under
 * which document category, and who may see the result. Antivirus scanning is
 * not implemented here: file_save_draft_area_files() already routes through
 * Moodle core's \core\antivirus\manager for any file saved via the standard
 * draft-area mechanism, which is the only mechanism this class uses.
 *
 * Deliberately staff-only in this phase: unlike contentvisible (shown to the
 * student themselves), attachments have no "shared with the student" tier —
 * the modelo mínimo of docs/fases/phase-5.md lists content/internal/
 * restricted as the entry's 3 text tiers, but says nothing about a
 * student-visible attachment tier, so building one would be inventing a
 * permission dimension nobody asked for. get_for_entry() always denies the
 * student themselves, regardless of any capability they might hold.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_service {

    /** @var string Moodle File API filearea for every tutoring entry attachment */
    public const FILEAREA = 'entryattachment';

    /**
     * @var string Fase 14 — a second filearea just for the SOP entry's
     * "Recomendaciones SOP" files, kept apart from "Informes facilitados"
     * (FILEAREA) so the SOP form can offer two independent upload boxes.
     */
    public const FILEAREA_SOP_RECOMMENDATION = 'entrysoprec';

    /**
     * Every filearea this service (and pluginfile) serves.
     *
     * @return string[]
     */
    public static function fileareas(): array {
        return [self::FILEAREA, self::FILEAREA_SOP_RECOMMENDATION];
    }

    /** @var entry_repository */
    private $entryrepository;

    /** @var entry_attachment_repository */
    private $repository;

    /** @var scope_service */
    private $scopeservice;

    public function __construct(
        ?entry_repository $entryrepository = null,
        ?entry_attachment_repository $repository = null,
        ?scope_service $scopeservice = null
    ) {
        $this->entryrepository = $entryrepository ?? new entry_repository();
        $this->repository = $repository ?? new entry_attachment_repository();
        $this->scopeservice = $scopeservice ?? new scope_service();
    }

    /**
     * Moves files out of a draft area into the permanent entryattachment
     * filearea for $entryid (file_save_draft_area_files(), the standard
     * Moodle mechanism — antivirus scanning already happened during upload,
     * before the files even reached the draft area), then records a
     * local_tut_entryattachment row for every file that does not already
     * have one, under $category.
     *
     * @param int $entryid
     * @param int $draftitemid
     * @param string $category one of entry_attachment_category::values()
     * @param int $userid
     * @return int how many new attachment rows were created
     */
    public function save_uploaded_files(
        int $entryid,
        int $draftitemid,
        string $category,
        int $userid,
        string $filearea = self::FILEAREA
    ): int {
        $existing = $this->entryrepository->get($entryid);

        if ($existing->status !== entry_status::ACTIVE) {
            throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
        }

        if (!in_array($category, entry_attachment_category::values(), true)) {
            throw new \moodle_exception('error_entry_attachment_category_invalid', 'local_monlaututoria');
        }
        if (!in_array($filearea, self::fileareas(), true)) {
            throw new \moodle_exception('error_entry_attachment_category_invalid', 'local_monlaututoria');
        }

        $context = \context_system::instance();
        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_monlaututoria',
            $filearea,
            $entryid,
            ['subdirs' => 0]
        );

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_monlaututoria', $filearea, $entryid, 'filename', false);

        $newcount = 0;
        foreach ($files as $file) {
            $pathnamehash = $file->get_pathnamehash();
            if ($this->repository->exists_for_pathnamehash($pathnamehash)) {
                continue;
            }

            $this->repository->create((object) [
                'entryid'      => $entryid,
                'pathnamehash' => $pathnamehash,
                'category'     => $category,
                'createdby'    => $userid,
            ]);
            $newcount++;
        }

        if ($newcount > 0) {
            entry_attachment_added::create_from_id(
                $entryid,
                $userid,
                (int) $existing->studentid,
                $newcount,
                $category
            )->trigger();
        }

        return $newcount;
    }

    /**
     * Returns every attachment of an entry, each paired with its category —
     * staff-only, same scope_service check as every other page exposing a
     * specific student's data, plus a hard floor identical in spirit to
     * entry_service's content masking: the student themselves never gets
     * anything back here, no matter what capability they hold.
     *
     * @param int $entryid
     * @param int $viewerid
     * @return array{0: \stored_file, 1: \stdClass|null}[] each pair is
     *         [stored_file, the local_tut_entryattachment row or null if
     *         somehow untracked]
     */
    public function get_for_entry(int $entryid, int $viewerid): array {
        $existing = $this->entryrepository->get($entryid);

        $this->scopeservice->require_user_can_access_student(
            $viewerid,
            (int) $existing->studentid,
            (int) $existing->academicyearid
        );

        $context = \context_system::instance();
        $isstudent = $viewerid === (int) $existing->studentid;
        // Fase 14 — a SOP entry's attachments are visible to any staff who
        // passed the scope check above (tutor of this student, or coordination):
        // the viewinternalnotes capability is not required in simple mode, the
        // same as the SOP entry's own content (entry_service::mask_content()).
        $issopsimple = !$isstudent
            && ($existing->entrykind ?? 'regular') === \local_monlaututoria\domain\entry_kind::SOP
            && \local_monlaututoria\feature::simple_mode();
        if ($isstudent
            || (!$issopsimple && !has_capability('local/monlaututoria:viewinternalnotes', $context, $viewerid))) {
            throw new \moodle_exception('error_scope_access_denied', 'local_monlaututoria');
        }

        $fs = get_file_storage();
        $files = [];
        foreach (self::fileareas() as $area) {
            $files += $fs->get_area_files($context->id, 'local_monlaututoria', $area, $entryid, 'filename', false);
        }
        $metadatabypathnamehash = $this->repository->get_for_entry($entryid);

        $pairs = [];
        foreach ($files as $file) {
            $pairs[] = [$file, $metadatabypathnamehash[$file->get_pathnamehash()] ?? null];
        }

        return $pairs;
    }
}
