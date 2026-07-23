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

namespace local_monlaututoria\repository;

use local_monlaututoria\domain\academic_year;

/**
 * Data access for local_tut_academicyear. No business rules here, only DML.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class academic_year_repository {

    /** @var string */
    private const TABLE = 'local_tut_academicyear';

    /**
     * Inserts a new academic year and returns its id.
     *
     * @param \stdClass $data must contain name, shortname, startdate, enddate, createdby
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->name = $data->name;
        $record->shortname = $data->shortname;
        $record->startdate = (int) $data->startdate;
        $record->enddate = (int) $data->enddate;
        $record->active = 0;
        $record->locked = 0;
        $record->createdby = (int) $data->createdby;
        $record->modifiedby = (int) $data->createdby;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Returns the raw record for an academic year, or throws if missing.
     *
     * @param int $id
     * @return \stdClass
     */
    public function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * Returns a typed DTO for an academic year.
     *
     * @param int $id
     * @return academic_year
     */
    public function get_dto(int $id): academic_year {
        return academic_year::from_record($this->get($id));
    }

    /**
     * Returns all academic years ordered by start date.
     *
     * @return \stdClass[]
     */
    public function get_all(): array {
        global $DB;

        return $DB->get_records(self::TABLE, null, 'startdate ASC');
    }

    /**
     * @param string $shortname
     * @return \stdClass|null
     */
    public function find_by_shortname(string $shortname): ?\stdClass {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['shortname' => $shortname]);

        return $record !== false ? $record : null;
    }

    /**
     * Returns the currently active academic year, or null if none is active.
     *
     * @return \stdClass|null
     */
    public function get_active(): ?\stdClass {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['active' => 1]);

        return $record !== false ? $record : null;
    }

    /**
     * Returns true if another academic year with the same shortname already exists.
     *
     * @param string $shortname
     * @param int|null $excludeid record to ignore, e.g. the one being edited
     * @return bool
     */
    public function shortname_exists(string $shortname, ?int $excludeid = null): bool {
        global $DB;

        $params = ['shortname' => $shortname];
        $sql = 'shortname = :shortname';
        if ($excludeid !== null) {
            $sql .= ' AND id <> :excludeid';
            $params['excludeid'] = $excludeid;
        }

        return $DB->record_exists_select(self::TABLE, $sql, $params);
    }

    /**
     * Updates name/shortname/dates. Does not touch active/locked (see set_active_flag/set_locked_flag).
     *
     * @param \stdClass $data must contain id; may contain name, shortname, startdate, enddate
     * @param int $modifiedby
     * @return bool
     */
    public function update(\stdClass $data, int $modifiedby): bool {
        global $DB;

        $record = $this->get((int) $data->id);
        $record->name = $data->name ?? $record->name;
        $record->shortname = $data->shortname ?? $record->shortname;
        $record->startdate = isset($data->startdate) ? (int) $data->startdate : $record->startdate;
        $record->enddate = isset($data->enddate) ? (int) $data->enddate : $record->enddate;
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record(self::TABLE, $record);
    }

    /**
     * Deletes an academic year row. Callers are responsible for enforcing the
     * business guard (not active, not locked, no dependent data) before calling this.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        global $DB;

        return $DB->delete_records(self::TABLE, ['id' => $id]);
    }

    /**
     * Sets the active flag for a single record.
     *
     * @param int $id
     * @param bool $active
     * @param int $modifiedby
     */
    public function set_active_flag(int $id, bool $active, int $modifiedby): void {
        global $DB;

        $DB->set_field(self::TABLE, 'active', $active ? 1 : 0, ['id' => $id]);
        $DB->set_field(self::TABLE, 'modifiedby', $modifiedby, ['id' => $id]);
        $DB->set_field(self::TABLE, 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Clears the active flag on every currently active row.
     *
     * @param int $modifiedby
     * @return int[] ids that were active before this call
     */
    public function clear_active(int $modifiedby): array {
        global $DB;

        $ids = $DB->get_fieldset_select(self::TABLE, 'id', 'active = :active', ['active' => 1]);
        if (!empty($ids)) {
            [$insql, $inparams] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $DB->set_field_select(self::TABLE, 'active', 0, "id $insql", $inparams);
            $DB->set_field_select(self::TABLE, 'modifiedby', $modifiedby, "id $insql", $inparams);
            $DB->set_field_select(self::TABLE, 'timemodified', time(), "id $insql", $inparams);
        }

        return array_map('intval', $ids);
    }

    /**
     * Sets the locked flag.
     *
     * @param int $id
     * @param bool $locked
     * @param int $modifiedby
     */
    public function set_locked_flag(int $id, bool $locked, int $modifiedby): void {
        global $DB;

        $DB->set_field(self::TABLE, 'locked', $locked ? 1 : 0, ['id' => $id]);
        $DB->set_field(self::TABLE, 'modifiedby', $modifiedby, ['id' => $id]);
        $DB->set_field(self::TABLE, 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Whether this academic year is referenced by data from later phases
     * (tutor assignments, tutoring entries, etc.).
     *
     * @todo Extend once local_tut_assignment / local_tut_entry (or equivalent)
     *       tables exist in a future phase. Today no such table exists, so this
     *       always returns false; the academic_year_service still blocks deletion
     *       of active/locked years independently of this check.
     *
     * @param int $id
     * @return bool
     */
    public function has_dependent_data(int $id): bool {
        return false;
    }
}
