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
 * CSV assignment import: upload + previsualización (phase 3D.2). No
 * application/execution here yet — that is phase 3D.3. The uploaded file
 * lives only in the current user's Moodle draft file area (standard,
 * self-cleaning temporary storage); this plugin never stores it permanently.
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

// The exclude form always carries a positive draftitemid (set via set_data()
// before display); the upload form's own file field is named differently
// ("csvfile"), so this distinguishes which of the two forms was submitted
// without relying on guesswork.
$posteddraftitemid = optional_param('draftitemid', 0, PARAM_INT);

if ($posteddraftitemid > 0) {
    require_sesskey();

    $delimiter = required_param('delimiter', PARAM_RAW);
    $encoding = required_param('encoding', PARAM_ALPHANUMEXT);
    if (!in_array($delimiter, $allowedelimiters, true) || !in_array($encoding, $allowedencodings, true)) {
        throw new \moodle_exception('error_csv_invalid_parameters', 'local_monlaututoria');
    }

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $posteddraftitemid, 'id', false);
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
    $excludeform->set_data((object) [
        'draftitemid' => $posteddraftitemid,
        'delimiter'   => $delimiter,
        'encoding'    => $encoding,
    ]);
} else if (($uploaddata = $uploadform->get_data()) !== null) {
    $content = $uploadform->get_file_content('csvfile');
    if ($content === false) {
        throw new \moodle_exception('error_csv_file_not_usable', 'local_monlaututoria');
    }

    $preview = $previewservice->preview($content, $uploaddata->delimiter, $uploaddata->encoding, (int) $USER->id);

    $excludeform = new \local_monlaututoria\form\csv_import_exclude_form(null, ['rows' => $preview->rows]);
    $excludeform->set_data((object) [
        'draftitemid' => (int) $uploaddata->csvfile,
        'delimiter'   => $uploaddata->delimiter,
        'encoding'    => $uploaddata->encoding,
    ]);
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('csv_import_title', 'local_monlaututoria'));

if ($preview === null) {
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

    echo $OUTPUT->notification(get_string('csv_apply_not_available_yet', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();
