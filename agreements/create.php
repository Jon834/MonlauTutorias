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
 * Agreement creation (phase 6.1). Always reached from a specific tutoring
 * entry ("entryid" param, e.g. a "Crear acuerdo" link on entries/view.php) —
 * never a free-standing student picker, same reasoning as entries/create.php.
 *
 * Security: local/monlaututoria:createagreement + scope_service, resolved
 * against the origin entry's own studentid/academicyearid — same 2-layer
 * pattern as every other page in this plugin.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::AGREEMENTS);
require_capability('local/monlaututoria:createagreement', $context);

$entryid = required_param('entryid', PARAM_INT);

$entryrepository = new \local_monlaututoria\repository\entry_repository();
$entry = $entryrepository->get($entryid);

$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student((int) $USER->id, (int) $entry->studentid, (int) $entry->academicyearid);

$student = core_user::get_user((int) $entry->studentid);
if (!$student || !empty($student->deleted)) {
    throw new \moodle_exception('invaliduserid');
}

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/agreements/create.php', ['entryid' => $entryid]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('agreement_create_title', 'local_monlaututoria', fullname($student)));
$PAGE->set_heading(get_string('agreement_create_title', 'local_monlaututoria', fullname($student)));

$form = new \local_monlaututoria\form\agreement_create_form();
$form->set_data((object) ['entryid' => $entryid]);

$returnurl = new moodle_url('/local/monlaututoria/entries/view.php', ['id' => $entryid]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $command = new \local_monlaututoria\domain\agreement_create_command(
        (int) $data->entryid,
        $data->description,
        $data->responsibletype,
        !empty($data->responsibleuserid) ? (int) $data->responsibleuserid : null,
        trim((string) $data->responsibleexternalname) !== '' ? $data->responsibleexternalname : null,
        (int) $data->duedate,
        !empty($data->visibletostudent)
    );

    $service = new \local_monlaututoria\service\agreement_service();
    $service->create($command, (int) $USER->id);

    redirect(
        $returnurl,
        get_string('agreement_create_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('agreement_create_title', 'local_monlaututoria', fullname($student)));
echo $renderer->contextual_help(
    get_string('help_concept_agreement_title', 'local_monlaututoria'),
    html_writer::tag('p', get_string('help_concept_agreement_short', 'local_monlaututoria'))
    . html_writer::tag('p', get_string('help_concept_agreement_full', 'local_monlaututoria'))
);
$form->display();
echo $OUTPUT->footer();
