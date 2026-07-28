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

/**
 * Read-only access to Moodle core's "additional user profile fields"
 * (Site administration > Users > Profile fields — user_info_field/
 * user_info_data), used to filter the coordination dashboard by an optional
 * field such as "Departamento" (phase 8 follow-up). This plugin never
 * defines, installs or writes to these tables — the field is expected to
 * already exist, created and maintained by a site administrator through
 * Moodle's own profile field UI, same as this plugin already treats cohorts
 * as externally managed data it only reads.
 *
 * Deliberately tolerant of the field not existing at all: get_menu_options()
 * returns an empty array rather than throwing, so a page can simply skip
 * rendering the filter when the site has not set the field up — "campo de
 * perfil opcional" means optional at the Moodle level too, not just per user.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class user_profile_field_repository {

    /**
     * The configured choices of a "menu of choices" profile field, in the
     * order the site administrator listed them — same order a Moodle profile
     * field select would show. Returns [] when no such field exists (any
     * datatype other than 'menu') or it has no options configured yet.
     *
     * @param string $shortname
     * @return string[]
     */
    public function get_menu_options(string $shortname): array {
        global $DB;

        $field = $DB->get_record('user_info_field', ['shortname' => $shortname, 'datatype' => 'menu']);
        if (!$field || trim((string) $field->param1) === '') {
            return [];
        }

        $options = array_map('trim', explode("\n", $field->param1));

        return array_values(array_filter($options, static fn (string $option): bool => $option !== ''));
    }

    /**
     * @param string $shortname
     * @param string $value exact match against user_info_data.data
     * @return int[] userids
     */
    public function get_userids_with_value(string $shortname, string $value): array {
        global $DB;

        $sql = "SELECT d.userid
                  FROM {user_info_data} d
                  JOIN {user_info_field} f ON f.id = d.fieldid
                 WHERE f.shortname = :shortname AND d.data = :value";

        return array_map('intval', $DB->get_fieldset_sql($sql, ['shortname' => $shortname, 'value' => $value]));
    }
}
