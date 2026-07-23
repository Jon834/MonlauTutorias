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
 * The 5 states a CSV import row can be in during previsualización (phase
 * 3D.2), as named in docs/fases/phase-3d.md.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_row_status {

    /** @var string ready to apply as-is */
    public const VALID = 'valid';

    /** @var string applicable, but with something worth reviewing (e.g. an unresolved optional column) */
    public const WARNING = 'warning';

    /** @var string would collide with existing data (duplicate, another active primary tutor...) */
    public const CONFLICT = 'conflict';

    /** @var string cannot be applied (missing/invalid data, unresolved required identifier, locked year...) */
    public const ERROR = 'error';

    /** @var string manually excluded by the administrator before confirming */
    public const EXCLUDED = 'excluded';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::VALID, self::WARNING, self::CONFLICT, self::ERROR, self::EXCLUDED];
    }
}
