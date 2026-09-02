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
 * Values for local_tut_entry.entrykind (fase 14).
 *
 * A 'sop' entry is recorded by the student's SOP orientation tutor (assigned
 * as a co_tutor). It carries an extra "Recomendaciones SOP" field and is
 * NEVER visible to the student — enforced in entry_service, not the template.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entry_kind {

    /** @var string An ordinary tutoring entry. */
    public const REGULAR = 'regular';

    /** @var string A SOP (orientación psicopedagógica) entry. */
    public const SOP = 'sop';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::REGULAR, self::SOP];
    }
}
