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
 * CSV assignment import: upload + previsualización (phase 3D.2) + aplicación
 * (phase 3D.3) + informe, exportación de errores y diferimiento a tarea ad
 * hoc para archivos grandes (phase 3D.4). The uploaded file lives only in
 * the current user's Moodle draft file area for small imports (standard,
 * self-cleaning temporary storage); for large imports (more than
 * csv_import_dispatch_service::LARGE_IMPORT_THRESHOLD rows), it is copied
 * into this plugin's own file area just long enough for the ad hoc task to
 * process it — see csv_import_dispatch_service and process_csv_import_task.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:importassignments', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/import.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('csv_import_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('csv_import_title', 'local_monlaututoria'));

$allowedelimiters = [',', ';', "\t"];
$allowedencodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];

$previewservice = new \local_monlaututoria\service\csv_import_preview_service();

$uploadform = new \local_monlaututoria\form\csv_import_upload_form();
$excludeform = null;
$preview = null;
$applyresult = null;
$applydeferred = false;
$applyformforerrors = null;
$draftitemid = null;
$delimiter = null;
$encoding = null;

// Three possible submissions on this one page, disambiguated by a field only
// that specific form carries: the apply form always posts a non-empty
// applyoperationuuid; the exclude form always posts a positive draftitemid
// (but never applyoperationuuid); the upload form's own file field is named
// differently ("csvfile") and is read via the form API instead.
$postedapplyuuid = optional_param('applyoperationuuid', '', PARAM_ALPHANUMEXT);
$posteddraftitemid = optional_param('draftitemid', 0, PARAM_INT);

if ($postedapplyuuid !== '') {
    require_sesskey();

    $delimiter = required_param('delimiter', PARAM_RAW);
    $encoding = required_param('encoding', PARAM_ALPHANUMEXT);
    if (!in_array($delimiter, $allowedelimiters, true) || !in_array($encoding, $allowedencodings, true)) {
        throw new \moodle_exception('error_csv_invalid_parameters', 'local_monlaututoria');
    }
    $draftitemid = $posteddraftitemid;

    $applyform = new \local_monlaututoria\form\csv_import_apply_form();
    if (($applydata = $applyform->get_data()) !== null) {
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
        $file = reset($files);
        if (!$file) {
            throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
        }
        $content = $file->get_content();

        $dispatchservice = new \local_monlaututoria\service\csv_import_dispatch_service();
        $applyresult = $dispatchservice->dispatch(
            $postedapplyuuid,
            $content,
            $delimiter,
            $encoding,
            $applydata->strategy,
            (int) $USER->id,
            !empty($applydata->allowreassignconflicts),
            $draftitemid
        );

        if ($applyresult === null) {
            // Deferred to an ad hoc task: there is nothing to show inline or
            // to build a report from yet — see docs/modelo-datos.md for how
            // to check on it later.
            $applydeferred = true;
        } else {
            // Single-use, per-user download of the "not applied" rows report
            // (phase 3D.4): the apply result is never persisted, so the only
            // window in which it can be downloaded is this same session,
            // before a later request overwrites or clears it.
            $SESSION->local_monlaututoria_csv_apply_report = (object) [
                'operationuuid' => $applyresult->operationuuid,
                'userid'        => (int) $USER->id,
                'result'        => $applyresult,
            ];
        }
    } else {
        // Validation failed (e.g. confirmation checkbox not ticked): redisplay
        // the same form with its own error messages, nothing else to compute.
        $applyformforerrors = $applyform;
    }
} else if ($posteddraftitemid > 0) {
    require_sesskey();

    $delimiter = required_param('delimiter', PARAM_RAW);
    $encoding = required_param('encoding', PARAM_ALPHANUMEXT);
    if (!in_array($delimiter, $allowedelimiters, true) || !in_array($encoding, $allowedencodings, true)) {
        throw new \moodle_exception('error_csv_invalid_parameters', 'local_monlaututoria');
    }
    $draftitemid = $posteddraftitemid;

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    $file = reset($files);
    if (!$file) {
        throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
    }
    $content = $file->get_content();

    $excludedrownumbers = [];
    $submitted = data_submitted();
    if ($submitted) {
        foreach ($submitted as $key => $value) {
            if (strpos($key, 'exclude_') === 0 && clean_param($value, PARAM_BOOL)) {
                $excludedrownumbers[] = clean_param(substr($key, strlen('exclude_')), PARAM_INT);
            }
        }
    }

    $preview = $previewservice->preview($content, $delimiter, $encoding, (int) $USER->id, $excludedrownumbers);

    $excludeform = new \local_monlaututoria\form\csv_import_exclude_form(null, ['rows' => $preview->rows]);
    $excludeform->set_data((object) ['draftitemid' => $draftitemid, 'delimiter' => $delimiter, 'encoding' => $encoding]);
} else if (($uploaddata = $uploadform->get_data()) !== null) {
    $content = $uploadform->get_file_content('csvfile');
    if ($content === false) {
        throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
    }

    $draftitemid = (int) $uploaddata->csvfile;
    $delimiter = $uploaddata->delimiter;
    $encoding = $uploaddata->encoding;

    $preview = $previewservice->preview($content, $delimiter, $encoding, (int) $USER->id);

    $excludeform = new \local_monlaututoria\form\csv_import_exclude_form(null, ['rows' => $preview->rows]);
    $excludeform->set_data((object) ['draftitemid' => $draftitemid, 'delimiter' => $delimiter, 'encoding' => $encoding]);
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('csv_import_title', 'local_monlaututoria'));

if ($applydeferred) {
    echo $OUTPUT->heading(get_string('csv_apply_result_title', 'local_monlaututoria'), 3);
    echo $OUTPUT->notification(
        get_string('csv_apply_deferred', 'local_monlaututoria'),
        \core\output\notification::NOTIFY_INFO
    );
} else if ($applyresult !== null) {
    echo $OUTPUT->heading(get_string('csv_apply_result_title', 'local_monlaututoria'), 3);
    echo html_writer::alist([
        get_string('csv_apply_created', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\csv_import_row_outcome::CREATED
        )),
        get_string('csv_apply_reassigned', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\csv_import_row_outcome::REASSIGNED
        )),
        get_string('csv_apply_nochange', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\csv_import_row_outcome::NO_CHANGE
        )),
        get_string('csv_apply_skipped', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_CONFLICT
        ) + $applyresult->count(\local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_ERROR)
          + $applyresult->count(\local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_EXCLUDED)),
        get_string('csv_apply_failed', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\csv_import_row_outcome::FAILED
        )),
    ]);
    $statuskey = $applyresult->finalstatus === \local_monlaututoria\domain\bulk_operation_status::FAILED
        ? 'csv_apply_status_failed'
        : ($applyresult->finalstatus === \local_monlaututoria\domain\bulk_operation_status::COMPLETED_WITH_ERRORS
            ? 'csv_apply_status_completed_with_errors'
            : 'csv_apply_status_completed');
    $notificationtype = $applyresult->finalstatus === \local_monlaututoria\domain\bulk_operation_status::FAILED
        ? \core\output\notification::NOTIFY_ERROR
        : (\local_monlaututoria\domain\bulk_operation_status::COMPLETED_WITH_ERRORS === $applyresult->finalstatus
            ? \core\output\notification::NOTIFY_WARNING
            : \core\output\notification::NOTIFY_SUCCESS);
    echo $OUTPUT->notification(get_string($statuskey, 'local_monlaututoria'), $notificationtype);

    echo $renderer->csv_import_apply_result_table($applyresult->rows);

    $exportservice = new \local_monlaututoria\service\csv_import_error_export_service();
    if (!empty($exportservice->rows($applyresult))) {
        $reporturl = new moodle_url('/local/monlaututoria/assignments/import_report.php', [
            'operationuuid' => $applyresult->operationuuid,
            'sesskey'       => sesskey(),
        ]);
        echo $OUTPUT->single_button($reporturl, get_string('csv_report_download', 'local_monlaututoria'), 'get');
    }
} else if ($applyformforerrors !== null) {
    echo $OUTPUT->heading(get_string('csv_apply_title', 'local_monlaututoria'), 3);
    $applyformforerrors->display();
} else if ($preview === null) {
    echo $OUTPUT->box(get_string('csv_import_intro', 'local_monlaututoria'));
    $uploadform->display();
} else {
    echo $OUTPUT->heading(get_string('csv_preview_summary_title', 'local_monlaututoria'), 3);
    echo html_writer::alist([
        get_string('csv_summary_total', 'local_monlaututoria', $preview->summary->totalrows),
        get_string('csv_summary_valid', 'local_monlaututoria', $preview->summary->validcount),
        get_string('csv_summary_warning', 'local_monlaututoria', $preview->summary->warningcount),
        get_string('csv_summary_conflict', 'local_monlaututoria', $preview->summary->conflictcount),
        get_string('csv_summary_error', 'local_monlaututoria', $preview->summary->errorcount),
        get_string('csv_summary_excluded', 'local_monlaututoria', $preview->summary->excludedcount),
    ]);

    echo $renderer->csv_import_preview_table($preview->rows);

    echo $OUTPUT->heading(get_string('csv_exclude_title', 'local_monlaututoria'), 3);
    echo $OUTPUT->box(get_string('csv_exclude_intro', 'local_monlaututoria'));
    $excludeform->display();

    echo $OUTPUT->heading(get_string('csv_apply_title', 'local_monlaututoria'), 3);
    echo $OUTPUT->box(get_string('csv_apply_intro', 'local_monlaututoria'));
    $applyform = new \local_monlaututoria\form\csv_import_apply_form();
    $applyform->set_data((object) [
        'applyoperationuuid' => $preview->operationuuid,
        'draftitemid'        => $draftitemid,
        'delimiter'          => $delimiter,
        'encoding'           => $encoding,
    ]);
    $applyform->display();
}

echo $OUTPUT->footer();
