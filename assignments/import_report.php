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

/**
 * Downloads the "not applied" rows CSV report for a just-applied CSV import
 * (phase 3D.4). The apply result is never persisted to the database — only
 * held in the current user's session, for this same browsing session, right
 * after assignments/import.php applied it — so this page can only serve the
 * download once, immediately after applying; it is not a permanent link.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:importassignments', $context);
require_sesskey();

$operationuuid = required_param('operationuuid', PARAM_ALPHANUMEXT);

$stored = $SESSION->local_monlaututoria_csv_apply_report ?? null;
if (
    $stored === null
    || $stored->operationuuid !== $operationuuid
    || (int) $stored->userid !== (int) $USER->id
) {
    throw new \moodle_exception('error_csv_report_not_available', 'local_monlaututoria');
}

// Single-use: clear it before generating the download, so a stale link
// (e.g. reused after the tab was reopened) cannot serve the same report twice
// and the session does not carry it indefinitely.
$result = $stored->result;
unset($SESSION->local_monlaututoria_csv_apply_report);

$exportservice = new \local_monlaututoria\service\csv_import_error_export_service();
$rows = $exportservice->rows($result);

$bulkoperationrepository = new \local_monlaututoria\repository\bulk_operation_repository();
$operation = $bulkoperationrepository->get_by_uuid($result->operationuuid);

\local_monlaututoria\event\csv_error_report_downloaded::create_from_operation(
    (int) $operation->id,
    (int) $USER->id,
    count($rows)
)->trigger();

$exportservice->download($result);
