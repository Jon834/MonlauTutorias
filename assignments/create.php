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
 * Manual creation of a tutor-student assignment.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:assignstudents', $context);
$canoverridelock = has_capability('local/monlaututoria:overridelock', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/create.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('assignment_create_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('assignment_create_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

// Only cohorts an admin has enabled for this plugin (cohort_visibility.php)
// — defaults to every Moodle cohort until an admin curates a subset.
$cohortoptions = [];
foreach ((new \local_monlaututoria\service\cohort_visibility_service())->get_visible_cohorts() as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}

$form = new \local_monlaututoria\form\assignment_form(null, [
    'academicyears' => $academicyearoptions,
    'cohorts'       => $cohortoptions,
]);

$returnurl = new moodle_url('/local/monlaututoria/assignments/index.php');

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $service = new \local_monlaututoria\service\assignment_service();
    $id = $service->create((object) [
        'studentid'      => (int) $data->studentid,
        'tutorid'        => (int) $data->tutorid,
        'academicyearid' => (int) $data->academicyearid,
        'cohortid'       => !empty($data->cohortid) ? (int) $data->cohortid : null,
        'assignmenttype' => $data->assignmenttype,
        // Derived, not a separate form field — see assignment_form's
        // definition() for why: a standalone checkbox let "Tipo" and
        // "isprimary" disagree, creating a row labelled "Tutor principal"
        // that dashboard_service/block_monlaututoria/reassign_primary_tutor()
        // (all keyed on isprimary=1) would silently never count.
        'isprimary'      => $data->assignmenttype === \local_monlaututoria\domain\assignment_type::PRIMARY,
        'timestart'      => $data->timestart,
        'timeend'        => !empty($data->timeend) ? $data->timeend : null,
        'note'           => $data->note !== '' ? $data->note : null,
    ], (int) $USER->id, false, $canoverridelock);

    redirect(
        new moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $id]),
        get_string('assignment_create_success', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$cohortcreateaction = [];
if (has_capability('local/monlaututoria:managecohortassignments', $context)
    && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::IMPORTS)) {
    $cohortcreateaction[] = [
        'url' => new moodle_url('/local/monlaututoria/assignments/cohort_create.php'),
        'label' => get_string('cohort_assignment_create', 'local_monlaututoria'),
        'title' => get_string('cohort_assignment_create_tip', 'local_monlaututoria'),
    ];
}

echo $OUTPUT->header();
echo $renderer->plugin_navigation('assignments');
echo $renderer->page_header_card(
    get_string('assignment_create_title', 'local_monlaututoria'),
    get_string('assignments_create_tip', 'local_monlaututoria'),
    $returnurl,
    get_string('page_back_assignments', 'local_monlaututoria'),
    $cohortcreateaction,
    get_string('pluginname', 'local_monlaututoria')
);
// This form is deliberately single-student only — "Cohorte" here is just a
// descriptive tag on this one manual row (see assignment_form's own
// docblock), never a bulk trigger. A real report from manual testing:
// picking a cohort here without also picking a student silently did
// nothing, because nothing on this screen ever assigned the whole group.
echo $OUTPUT->box(get_string('cohort_assignment_manual_hint', 'local_monlaututoria'));
$form->display();
echo $OUTPUT->footer();
