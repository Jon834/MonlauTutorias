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

use local_monlaututoria\repository\catalogue_repository;
use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;
use local_monlaututoria\event\reason_created;
use local_monlaututoria\event\reason_updated;
use local_monlaututoria\event\reason_activated;
use local_monlaututoria\event\modality_created;
use local_monlaututoria\event\modality_updated;
use local_monlaututoria\event\modality_activated;

/**
 * Single application service shared by the reason and modality catalogues,
 * instantiated once per type. Both catalogues have identical business rules
 * (shortname uniqueness, reordering, delete guard); only the event classes
 * triggered differ, since Moodle events must map to a single, fixed table.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class catalogue_service {

    public const TYPE_REASON = 'reason';
    public const TYPE_MODALITY = 'modality';

    /** @var catalogue_repository */
    private $repository;

    /** @var string */
    private $type;

    /**
     * @param string $type self::TYPE_REASON or self::TYPE_MODALITY
     * @param catalogue_repository|null $repository injected for testing; defaults per $type
     */
    public function __construct(string $type, ?catalogue_repository $repository = null) {
        if (!in_array($type, [self::TYPE_REASON, self::TYPE_MODALITY], true)) {
            throw new \coding_exception('Unknown catalogue type: ' . $type);
        }

        $this->type = $type;
        $this->repository = $repository ?? (
            $type === self::TYPE_REASON ? new reason_repository() : new modality_repository()
        );
    }

    /**
     * @param \stdClass $data must contain name, shortname; may contain description, sortorder,
     *                        and (for reasons) requiresfollowup, defaultvisibility
     * @param int $userid
     * @return int
     */
    public function create(\stdClass $data, int $userid): int {
        $this->validate_shortname_unique($data->shortname);

        $data->createdby = $userid;
        $id = $this->repository->create($data);

        $this->trigger_created($id, $userid);

        return $id;
    }

    /**
     * @param \stdClass $data must contain id
     * @param int $userid
     * @return bool
     */
    public function update(\stdClass $data, int $userid): bool {
        $id = (int) $data->id;

        if (!empty($data->shortname)) {
            $this->validate_shortname_unique($data->shortname, $id);
        }

        $result = $this->repository->update($data, $userid);

        $this->trigger_updated($id, $userid);

        return $result;
    }

    /**
     * @param int $id
     * @param bool $active
     * @param int $userid
     */
    public function set_active(int $id, bool $active, int $userid): void {
        $this->repository->set_active_flag($id, $active, $userid);

        $this->trigger_activated($id, $userid, $active);
    }

    /**
     * @param int $id
     * @param int $direction negative to move up, positive to move down
     */
    public function move(int $id, int $direction): void {
        $this->repository->move($id, $direction);
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void {
        if ($this->repository->has_dependent_data($id)) {
            throw new \moodle_exception($this->type . '_delete_blocked_used', 'local_monlaututoria');
        }

        $this->repository->delete($id);
    }

    /**
     * @param string $shortname
     * @param int|null $excludeid
     */
    private function validate_shortname_unique(string $shortname, ?int $excludeid = null): void {
        if ($this->repository->shortname_exists($shortname, $excludeid)) {
            throw new \moodle_exception('error_shortname_duplicate', 'local_monlaututoria');
        }
    }

    /**
     * @param int $id
     * @param int $userid
     */
    private function trigger_created(int $id, int $userid): void {
        $class = $this->type === self::TYPE_REASON ? reason_created::class : modality_created::class;
        $class::create_from_id($id, $userid)->trigger();
    }

    /**
     * @param int $id
     * @param int $userid
     */
    private function trigger_updated(int $id, int $userid): void {
        $class = $this->type === self::TYPE_REASON ? reason_updated::class : modality_updated::class;
        $class::create_from_id($id, $userid)->trigger();
    }

    /**
     * @param int $id
     * @param int $userid
     * @param bool $active
     */
    private function trigger_activated(int $id, int $userid, bool $active): void {
        $class = $this->type === self::TYPE_REASON ? reason_activated::class : modality_activated::class;
        $class::create_from_id($id, $userid, $active)->trigger();
    }
}
