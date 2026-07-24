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
 * Valid values for local_tut_agreement.responsibletype (docs/fases/phase-6.md:
 * "alumno, tutor, familia, docente, coordinación, orientación, empresa u otro").
 * A separate enum from entry_participant_type: the two lists overlap but are
 * not identical (this one adds student/tutor/coordination, entry's does not).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_responsible_type {

    public const STUDENT = 'student';
    public const TUTOR = 'tutor';
    public const FAMILY = 'family';
    public const TEACHER = 'teacher';
    public const COORDINATION = 'coordination';
    public const ORIENTATION = 'orientation';
    public const COMPANY = 'company';
    public const OTHER = 'other';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [
            self::STUDENT, self::TUTOR, self::FAMILY, self::TEACHER,
            self::COORDINATION, self::ORIENTATION, self::COMPANY, self::OTHER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        $options = [];
        foreach (self::values() as $value) {
            $options[$value] = get_string('agreementresponsibletype_' . $value, 'local_monlaututoria');
        }

        return $options;
    }
}
