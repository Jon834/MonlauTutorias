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
 * cohort_assignment_apply_service::apply_all() to unwind its transaction and
 * report which student caused the batch to fail. Never bubbles up to the
 * user — cohort_assignment_apply_service always catches it and turns it into
 * a proper error/event, so this deliberately does not extend
 * moodle_exception. Same pattern as csv_import_atomic_failure.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort_assignment_apply_failure extends \Exception {

    public function __construct(public readonly int $studentid) {
        parent::__construct('Cohort assignment apply batch failed at student id ' . $studentid);
    }
}
