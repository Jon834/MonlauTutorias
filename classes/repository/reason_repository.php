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

use local_monlaututoria\domain\reason;
use local_monlaututoria\domain\visibility_level;

/**
 * Data access for local_tut_reason.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reason_repository extends catalogue_repository {

    protected function get_table(): string {
        return 'local_tut_reason';
    }

    protected function dto_class(): string {
        return reason::class;
    }

    protected function apply_extra_fields_on_create(\stdClass $record, \stdClass $data): \stdClass {
        $record->requiresfollowup = !empty($data->requiresfollowup) ? 1 : 0;
        $record->defaultvisibility = $data->defaultvisibility ?? visibility_level::INTERNAL;

        return $record;
    }

    protected function apply_extra_fields_on_update(\stdClass $record, \stdClass $data): \stdClass {
        $record->requiresfollowup = property_exists($data, 'requiresfollowup')
            ? (!empty($data->requiresfollowup) ? 1 : 0)
            : $record->requiresfollowup;
        $record->defaultvisibility = $data->defaultvisibility ?? $record->defaultvisibility;

        return $record;
    }
}
