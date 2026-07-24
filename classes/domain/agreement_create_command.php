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
 * Command object for agreement_service::create() — named fields read better
 * than a loose stdClass here, same reasoning as entry_create_command.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_create_command {

    public function __construct(
        public readonly int $entryid,
        public readonly string $description,
        public readonly string $responsibletype,
        public readonly ?int $responsibleuserid,
        public readonly ?string $responsibleexternalname,
        public readonly int $duedate,
        public readonly bool $visibletostudent = false
    ) {
    }
}
