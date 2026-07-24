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
 * Valid persisted values for local_tut_agreement.status.
 *
 * docs/fases/phase-6.md lists 5 states ("pendiente, en curso, completado,
 * vencido, cancelado"), but "vencido"/overdue is deliberately NOT a 5th
 * persisted value here — it is computed at read time (status still
 * pending/in_progress and duedate already passed, see
 * agreement_repository::is_row_overdue()/search()'s 'overdue' filter). A
 * persisted 5th state would need a scheduled task flipping rows daily just
 * to keep it accurate, for a fact that is already fully derivable from
 * duedate + status on every read.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_status {

    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    /**
     * @return string[]
     */
    public static function values(): array {
        return [self::PENDING, self::IN_PROGRESS, self::COMPLETED, self::CANCELLED];
    }

    /**
     * @return string[] statuses that a row can still be "overdue" from
     */
    public static function open_values(): array {
        return [self::PENDING, self::IN_PROGRESS];
    }

    /**
     * @return array<string, string>
     */
    public static function get_options(): array {
        return [
            self::PENDING     => get_string('agreementstatus_pending', 'local_monlaututoria'),
            self::IN_PROGRESS => get_string('agreementstatus_in_progress', 'local_monlaututoria'),
            self::COMPLETED   => get_string('agreementstatus_completed', 'local_monlaututoria'),
            self::CANCELLED   => get_string('agreementstatus_cancelled', 'local_monlaututoria'),
        ];
    }
}
