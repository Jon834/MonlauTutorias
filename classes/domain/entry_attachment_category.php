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

namespace local_monlaututoria\domain;

/**
 * Document categories for tutoring entry attachments (phase 5.6). Moodle's
 * File API has no native per-file category field, hence
 * local_tut_entryattachment.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_attachment_category {

    public const REPORT = 'report';
    public const CONSENT = 'consent';
    public const EVIDENCE = 'evidence';
    public const OTHER = 'other';

    /** @var string Fase 14 — "Informes facilitados" (SOP entries). */
    public const SOP_REPORT = 'sop_report';

    /** @var string Fase 14 — "Recomendaciones SOP" attachments (SOP entries). */
    public const SOP_RECOMMENDATION = 'sop_recommendation';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::REPORT, self::CONSENT, self::EVIDENCE, self::OTHER,
            self::SOP_REPORT, self::SOP_RECOMMENDATION,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::REPORT   => get_string('entryattachmentcategory_report', 'local_monlaututoria'),
            self::CONSENT  => get_string('entryattachmentcategory_consent', 'local_monlaututoria'),
            self::EVIDENCE => get_string('entryattachmentcategory_evidence', 'local_monlaututoria'),
            self::OTHER    => get_string('entryattachmentcategory_other', 'local_monlaututoria'),
        ];
    }

    /**
     * Fase 14 — the two categories a SOP entry uses.
     *
     * @return array<string, string>
     */
    public static function get_sop_options(): array {
        return [
            self::SOP_REPORT         => get_string('entryattachmentcategory_sop_report', 'local_monlaututoria'),
            self::SOP_RECOMMENDATION => get_string('entryattachmentcategory_sop_recommendation', 'local_monlaututoria'),
        ];
    }
}
