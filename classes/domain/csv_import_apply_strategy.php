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
 * How a CSV import batch is applied (phase 3D.3).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_apply_strategy {

    /** @var string default: apply every valid row independently, record errors per row, keep going */
    public const PARTIAL_VALID = 'partial_valid';

    /** @var string all-or-nothing: one row failing rolls back the whole batch */
    public const ATOMIC_ALL = 'atomic_all';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PARTIAL_VALID, self::ATOMIC_ALL];
    }
}
