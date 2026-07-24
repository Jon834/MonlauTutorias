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

namespace local_monlaututoria\service;

/**
 * Internal control-flow signal used only within
 * csv_import_apply_service::apply_atomic() to unwind the transaction and
 * report which row caused an atomic_all batch to fail. Never bubbles up to
 * the user — csv_import_apply_service always catches it and turns it into a
 * proper result/event, so this deliberately does not extend moodle_exception.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_import_atomic_failure extends \Exception {

    public function __construct(public readonly int $rownumber) {
        parent::__construct('CSV import atomic batch failed at row ' . $rownumber);
    }
}
