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

use local_monlaututoria\domain\catalogue_item;

/**
 * Shared data access for the reason and modality catalogues. No business rules
 * here, only DML. Subclasses only need to declare their table and DTO class.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class catalogue_repository {

    /**
     * @return string the Moodle table name for this catalogue (without prefix)
     */
    abstract protected function get_table(): string;

    /**
     * @return class-string<catalogue_item> the DTO class for this catalogue
     */
    abstract protected function dto_class(): string;

    /**
     * Hook for subclasses to persist catalogue-specific extra fields on create.
     *
     * @param \stdClass $record
     * @param \stdClass $data
     * @return \stdClass
     */
    protected function apply_extra_fields_on_create(\stdClass $record, \stdClass $data): \stdClass {
        return $record;
    }

    /**
     * Hook for subclasses to persist catalogue-specific extra fields on update.
     *
     * @param \stdClass $record
     * @param \stdClass $data
     * @return \stdClass
     */
    protected function apply_extra_fields_on_update(\stdClass $record, \stdClass $data): \stdClass {
        return $record;
    }

    /**
     * Inserts a new catalogue item and returns its id.
     *
     * @param \stdClass $data must contain name, shortname, createdby; may contain description, active, sortorder
     * @return int
     */
    public function create(\stdClass $data): int {
        global $DB;

        $record = new \stdClass();
        $record->name = $data->name;
        $record->shortname = $data->shortname;
        $record->description = $data->description ?? null;
        $record->active = property_exists($data, 'active') ? (!empty($data->active) ? 1 : 0) : 1;
        $record->sortorder = $data->sortorder ?? $this->next_sortorder();
        $record->createdby = (int) $data->createdby;
        $record->modifiedby = (int) $data->createdby;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        $record = $this->apply_extra_fields_on_create($record, $data);

        return $DB->insert_record($this->get_table(), $record);
    }

    /**
     * Returns the raw record for a catalogue item, or throws if missing.
     *
     * @param int $id
     * @return \stdClass
     */
    public function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record($this->get_table(), ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * Returns a typed DTO for a catalogue item.
     *
     * @param int $id
     * @return catalogue_item
     */
    public function get_dto(int $id): catalogue_item {
        $class = $this->dto_class();

        return $class::from_record($this->get($id));
    }

    /**
     * Returns catalogue items ordered by sortorder, id as tie-breaker.
     *
     * @param bool $onlyactive
     * @return \stdClass[]
     */
    public function get_all(bool $onlyactive = false): array {
        global $DB;

        $conditions = $onlyactive ? ['active' => 1] : null;

        return $DB->get_records($this->get_table(), $conditions, 'sortorder ASC, id ASC');
    }

    /**
     * Returns true if another item with the same shortname already exists.
     *
     * @param string $shortname
     * @param int|null $excludeid
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

        return $DB->record_exists_select($this->get_table(), $sql, $params);
    }

    /**
     * Updates a catalogue item.
     *
     * @param \stdClass $data must contain id
     * @param int $modifiedby
     * @return bool
     */
    public function update(\stdClass $data, int $modifiedby): bool {
        global $DB;

        $record = $this->get((int) $data->id);
        $record->name = $data->name ?? $record->name;
        $record->shortname = $data->shortname ?? $record->shortname;
        $record->description = property_exists($data, 'description') ? $data->description : $record->description;
        $record = $this->apply_extra_fields_on_update($record, $data);
        $record->modifiedby = $modifiedby;
        $record->timemodified = time();

        return $DB->update_record($this->get_table(), $record);
    }

    /**
     * Deletes a catalogue item row. Callers must enforce business guards
     * (no dependent data) before calling this.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        global $DB;

        return $DB->delete_records($this->get_table(), ['id' => $id]);
    }

    /**
     * Sets the active flag.
     *
     * @param int $id
     * @param bool $active
     * @param int $modifiedby
     */
    public function set_active_flag(int $id, bool $active, int $modifiedby): void {
        global $DB;

        $DB->set_field($this->get_table(), 'active', $active ? 1 : 0, ['id' => $id]);
        $DB->set_field($this->get_table(), 'modifiedby', $modifiedby, ['id' => $id]);
        $DB->set_field($this->get_table(), 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Moves an item up or down by swapping sortorder with its neighbour.
     *
     * @param int $id
     * @param int $direction negative to move up, positive to move down
     */
    public function move(int $id, int $direction): void {
        global $DB;

        $direction = $direction <=> 0;
        if ($direction === 0) {
            return;
        }

        $items = array_values($this->get_all());
        $index = null;
        foreach ($items as $i => $item) {
            if ((int) $item->id === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return;
        }

        $swapwith = $index + $direction;
        if ($swapwith < 0 || $swapwith >= count($items)) {
            return;
        }

        $a = $items[$index];
        $b = $items[$swapwith];

        $transaction = $DB->start_delegated_transaction();
        $DB->set_field($this->get_table(), 'sortorder', $b->sortorder, ['id' => $a->id]);
        $DB->set_field($this->get_table(), 'sortorder', $a->sortorder, ['id' => $b->id]);
        $transaction->allow_commit();
    }

    /**
     * Whether this catalogue item is referenced by data from later phases
     * (tutoring entries, etc.).
     *
     * @todo Extend once local_tut_entry (or equivalent) exists in a future
     *       phase. Today no such table exists, so this always returns false.
     *
     * @param int $id
     * @return bool
     */
    public function has_dependent_data(int $id): bool {
        return false;
    }

    /**
     * Returns the next sortorder value for a new item (max + 1).
     *
     * @return int
     */
    private function next_sortorder(): int {
        global $DB;

        $max = $DB->get_field_sql('SELECT MAX(sortorder) FROM {' . $this->get_table() . '}');

        return ((int) $max) + 1;
    }
}
