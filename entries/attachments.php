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
 * Lists and uploads attachments for a tutoring entry (phase 5.6).
 * Deliberately staff-only — see entry_attachment_service's class docblock
 * for why there is no student-visible attachment tier in this phase.
 *
 * Viewing: local/monlaututoria:viewinternalnotes (+ scope_service, enforced
 * inside entry_attachment_service::get_for_entry()).
 * Uploading: editanyentry, or editownentry limited to entries this user
 * authored — same capability model as entries/edit.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$id = required_param('id', PARAM_INT);

$entryrepository = new \local_monlaututoria\repository\entry_repository();
$existing = $entryrepository->get($id);

$issop = ($existing->entrykind ?? 'regular') === \local_monlaututoria\domain\entry_kind::SOP;
// Fase 14 — attachments are hidden in simple mode EXCEPT on SOP entries.
if (!$issop) {
    \local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::ATTACHMENTS);
}

$attachmentservice = new \local_monlaututoria\service\entry_attachment_service($entryrepository);
// Enforces scope_service + viewinternalnotes (SOP-exempt in simple mode) +
// the student hard floor — throws before this page renders anything if the
// viewer is not entitled.
$pairs = $attachmentservice->get_for_entry($id, (int) $USER->id);

$isowner = ((int) $existing->createdby === (int) $USER->id);
$caneditownsimple = \local_monlaututoria\feature::simple_mode()
    && !(new \local_monlaututoria\service\scope_service())
        ->access_is_historical_only((int) $USER->id, (int) $existing->studentid);
$canupload = $existing->status === \local_monlaututoria\domain\entry_status::ACTIVE
    && (has_capability('local/monlaututoria:editanyentry', $context)
        || ($isowner && (has_capability('local/monlaututoria:editownentry', $context) || $caneditownsimple)));

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/attachments.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_attachments_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_attachments_title', 'local_monlaututoria'));

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $id]);

if ($canupload) {
    $form = new \local_monlaututoria\form\entry_attachment_form(null, [
        'categories' => $issop
            ? \local_monlaututoria\domain\entry_attachment_category::get_sop_options()
            : \local_monlaututoria\domain\entry_attachment_category::get_options(),
    ]);

    // A fresh, empty draft area every time — never preloaded from the
    // permanent entryattachment area, so this filemanager only ever offers
    // "add a new file", never "remove an existing one". Existing attachments
    // are shown as a separate, read-only list above instead.
    $draftitemid = file_get_submitted_draft_itemid('attachments');
    file_prepare_draft_area($draftitemid, null, 'user', 'draft', null);
    $form->set_data((object) ['id' => $id, 'attachments' => $draftitemid]);

    if ($form->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $form->get_data()) {
        // Fase 14 — "Recomendaciones SOP" files live in their own filearea.
        $targetarea = $data->category === \local_monlaututoria\domain\entry_attachment_category::SOP_RECOMMENDATION
            ? \local_monlaututoria\service\entry_attachment_service::FILEAREA_SOP_RECOMMENDATION
            : \local_monlaututoria\service\entry_attachment_service::FILEAREA;
        $newcount = $attachmentservice->save_uploaded_files($id, $data->attachments, $data->category, (int) $USER->id, $targetarea);

        redirect(
            new moodle_url('/local/monlaututoria/entries/attachments.php', ['id' => $id]),
            get_string('entry_attachment_upload_success', 'local_monlaututoria', $newcount),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_attachments_title', 'local_monlaututoria'));

if (empty($pairs)) {
    echo $OUTPUT->notification(get_string('entry_attachments_empty', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);
} else {
    $categoryoptions = \local_monlaututoria\domain\entry_attachment_category::get_options()
        + \local_monlaututoria\domain\entry_attachment_category::get_sop_options();
    $table = new html_table();
    $table->head = [
        get_string('entry_attachment_files', 'local_monlaututoria'),
        get_string('entry_attachment_category', 'local_monlaututoria'),
    ];
    foreach ($pairs as [$file, $metadata]) {
        $downloadurl = moodle_url::make_pluginfile_url(
            $context->id,
            'local_monlaututoria',
            $file->get_filearea(),
            $id,
            $file->get_filepath(),
            $file->get_filename(),
            true
        );
        $categorylabel = $metadata !== null ? ($categoryoptions[$metadata->category] ?? $metadata->category) : '—';
        $table->data[] = [
            html_writer::link($downloadurl, s($file->get_filename())),
            $categorylabel,
        ];
    }
    echo html_writer::div(html_writer::table($table), 'table-responsive');
}

if ($canupload) {
    $form->display();
}

echo $OUTPUT->footer();
