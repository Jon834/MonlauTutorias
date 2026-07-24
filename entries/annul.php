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
 * Annuls an active tutoring entry (phase 5.5): logical annulment, never a
 * physical delete — the row and its history remain in place with
 * status=annulled.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$id = required_param('id', PARAM_INT);

$repository = new \local_monlaututoria\repository\entry_repository();
$existing = $repository->get($id);

require_capability('local/monlaututoria:annulentry', $context);

if ($existing->status !== \local_monlaututoria\domain\entry_status::ACTIVE) {
    throw new \moodle_exception('error_entry_already_annulled', 'local_monlaututoria');
}

// Defense in depth: annulling a specific student's entry also goes through
// scope_service, on top of the annulentry capability above.
$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, (int) $existing->studentid, (int) $existing->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/annul.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_annul_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_annul_title', 'local_monlaututoria'));

$student = core_user::get_user((int) $existing->studentid);
$tutor = core_user::get_user((int) $existing->tutorid);
$dateformat = get_string('strftimedatefullshort', 'langconfig');

$summarylines = [
    get_string('assignment_col_student', 'local_monlaututoria') . ': '
        . ($student ? fullname($student) : '#' . $existing->studentid),
    get_string('assignment_col_tutor', 'local_monlaututoria') . ': '
        . ($tutor ? fullname($tutor) : '#' . $existing->tutorid),
    get_string('entry_field_entrydate', 'local_monlaututoria') . ': '
        . userdate((int) $existing->entrydate, $dateformat),
];
$summaryhtml = html_writer::alist(array_map('s', $summarylines));

$form = new \local_monlaututoria\form\entry_annul_form(null, ['summaryhtml' => $summaryhtml]);
$form->set_data((object) ['id' => $id]);

$returnurl = new moodle_url('/local/monlaututoria/student/view.php', [
    'id' => $existing->studentid, 'academicyearid' => $existing->academicyearid, 'tab' => 'tutorias',
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $id]));
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\entry_service($repository);
    $service->annul($id, (int) $USER->id, $data->reason);

    redirect(
        $returnurl,
        get_string('entry_annul_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('entry_annul_title', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
