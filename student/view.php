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
 * Student longitudinal file. "Resumen" (phase 4.1) and "Historial" (phase
 * 4.2) tabs have real content; "Tutorías"/"Acuerdos" stay empty until phases
 * 5/6 (see "Pestañas iniciales" in docs/fases/phase-4.md).
 *
 * Phase 4.3 ("Permisos y vistas"): a student viewing their OWN file
 * (local/monlaututoria:viewownfile, granted to every authenticated user by
 * default — see db/access.php for why "authenticated user", not "student",
 * is the correct default archetype here) gets a limited view — no links out to assignments/view.php
 * (which they have no capability to open, and which shows the
 * administrative note/closereason/createdby/modifiedby fields this phase's
 * "separar contenido visible del alumno de notas internas" requirement asks
 * to keep out of their reach), and the history table drops its
 * motivo/origen columns for the same reason. "Coordinación según ámbito" is
 * NOT addressed here: this project's scope model remains binary
 * (viewallassignments or nothing) — there is still no concept of "a
 * coordinator responsible for a subset of cohorts/students" to scope
 * against, the same documented gap already left open since phases 3B.5A/
 * 3C.1/3E.1. Building a fake narrower scope would misrepresent a control
 * that does not really exist.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$studentid = required_param('id', PARAM_INT);

$isself = ((int) $USER->id === $studentid);
$canviewownfile = $isself && has_capability('local/monlaututoria:viewownfile', $context);
if (!$canviewownfile) {
    require_capability('local/monlaututoria:viewstudent', $context);
}
// A student viewing their own file always gets the limited view, regardless
// of whatever other capability they might also happen to hold — this is
// about whose file is open, not about which capability let them in.
$islimitedview = $isself;

$requestedacademicyearid = optional_param('academicyearid', 0, PARAM_INT);
$tab = optional_param('tab', 'resumen', PARAM_ALPHA);
if (!in_array($tab, ['resumen', 'historial', 'tutorias', 'acuerdos'], true)) {
    $tab = 'resumen';
}

$student = core_user::get_user($studentid);
if (!$student || !empty($student->deleted)) {
    throw new \moodle_exception('invaliduserid');
}

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
if ($requestedacademicyearid > 0) {
    $academicyear = $academicyearrepository->find($requestedacademicyearid);
    if ($academicyear === null) {
        // A plugin-specific message instead of letting a generic
        // dml_missing_record_exception bubble up — same "clear error"
        // standard already applied to $student a few lines above, extended
        // here to a manipulated academicyearid param (phase 4.4).
        throw new \moodle_exception('error_invalidacademicyearid', 'local_monlaututoria');
    }
} else {
    $academicyear = $academicyearrepository->get_active();
}

// Scope is checked against the specific academic year being displayed, same
// as assignments/view.php — never widened to "any year" just because this
// page can show more than one.
$scope = new \local_monlaututoria\service\scope_service();
$scope->require_user_can_access_student(
    (int) $USER->id,
    $studentid,
    $academicyear !== null ? (int) $academicyear->id : null
);

$PAGE->set_context($context);
$urlparams = ['id' => $studentid, 'tab' => $tab];
if ($requestedacademicyearid > 0) {
    $urlparams['academicyearid'] = $requestedacademicyearid;
}
$PAGE->set_url('/local/monlaututoria/student/view.php', $urlparams);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('student_summary_title', 'local_monlaututoria'));
$PAGE->set_heading(fullname($student));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($student));
echo $OUTPUT->user_picture($student, ['size' => 100, 'class' => 'mb-3']);

$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}
if (!empty($academicyearoptions)) {
    $selectorurl = new moodle_url('/local/monlaututoria/student/view.php', ['id' => $studentid, 'tab' => $tab]);
    echo $OUTPUT->single_select(
        $selectorurl,
        'academicyearid',
        $academicyearoptions,
        $academicyear !== null ? (int) $academicyear->id : '',
        ['' => get_string('choosedots')],
        'academicyearselector'
    );
}

echo $renderer->student_tabs($tab, $studentid, $academicyear !== null ? (int) $academicyear->id : null);

if ($academicyear === null) {
    echo $renderer->noactiveacademicyear_warning();
} else if ($tab === 'resumen') {
    $summaryservice = new \local_monlaututoria\service\student_summary_service();
    $summary = $summaryservice->get_summary($studentid, (int) $academicyear->id);

    echo $renderer->student_summary($summary, $academicyear, $student, $islimitedview);
} else if ($tab === 'historial') {
    $statusfilter = optional_param('status', '', PARAM_ALPHA);
    $filters = [];
    if (in_array($statusfilter, \local_monlaututoria\domain\assignment_status::values(), true)) {
        $filters['status'] = $statusfilter;
    }

    $page = optional_param('page', 0, PARAM_INT);
    $perpage = 20;

    $assignmentrepository = new \local_monlaututoria\repository\assignment_repository();
    $totalcount = $assignmentrepository->count_search($filters + ['studentid' => $studentid]);
    $records = $assignmentrepository->search_history_for_student($studentid, $filters, $page * $perpage, $perpage);

    $tutorids = array_unique(array_map(static fn ($record) => (int) $record->tutorid, $records));
    $tutors = !empty($tutorids) ? $DB->get_records_list('user', 'id', $tutorids, '', 'id, firstname, lastname, email') : [];

    $academicyearids = array_unique(array_map(static fn ($record) => (int) $record->academicyearid, $records));
    $academicyears = !empty($academicyearids) ? $academicyearrepository->get_many($academicyearids) : [];

    $statusoptions = ['' => get_string('choosedots')] + \local_monlaututoria\domain\assignment_status::get_options();
    $statusurl = new moodle_url('/local/monlaututoria/student/view.php', array_filter([
        'id' => $studentid, 'tab' => 'historial', 'academicyearid' => $requestedacademicyearid ?: null,
    ]));
    echo $OUTPUT->single_select($statusurl, 'status', $statusoptions, $statusfilter, [], 'statusselector');

    echo $renderer->student_history_table($records, $tutors, $academicyears, $islimitedview);

    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);
} else if ($tab === 'tutorias') {
    if (!$islimitedview && has_capability('local/monlaututoria:createentry', $context)) {
        $entryurlparams = ['studentid' => $studentid, 'academicyearid' => (int) $academicyear->id];
        echo html_writer::div(
            $OUTPUT->single_button(
                new moodle_url('/local/monlaututoria/entries/create.php', $entryurlparams),
                get_string('entry_register', 'local_monlaututoria')
            ) .
            $OUTPUT->single_button(
                new moodle_url('/local/monlaututoria/entries/create_full.php', $entryurlparams),
                get_string('entry_full_register', 'local_monlaututoria')
            ),
            'd-flex gap-2'
        );
    }

    // Phase 5.4: real listing, replacing the placeholder. Filters: estado,
    // modalidad, and (limited view excepted, same reasoning as the
    // "Motivo"/"Origen" columns of the assignments history tab) motivo and
    // "visibilidad" (which content tier a row actually has populated for
    // this viewer — useful for a coordinator auditing which entries carry a
    // restricted note, for instance).
    $statusfilter = optional_param('entrystatus', '', PARAM_ALPHA);
    $modalityfilter = optional_param('modalityid', 0, PARAM_INT);
    $reasonfilter = $islimitedview ? 0 : optional_param('reasonid', 0, PARAM_INT);
    $visibilityfilter = optional_param('visibilitytier', '', PARAM_ALPHA);

    $entryfilters = [];
    if (in_array($statusfilter, \local_monlaututoria\domain\entry_status::values(), true)) {
        $entryfilters['status'] = $statusfilter;
    }
    if ($modalityfilter > 0) {
        $entryfilters['modalityid'] = $modalityfilter;
    }
    if ($reasonfilter > 0) {
        $entryfilters['reasonid'] = $reasonfilter;
    }
    if (in_array($visibilityfilter, ['contentvisible', 'noteinternal', 'noterestricted'], true)) {
        $entryfilters['visibilitytier'] = $visibilityfilter;
    }

    $entrypage = optional_param('entrypage', 0, PARAM_INT);
    $entryperpage = 20;

    $entryservice = new \local_monlaututoria\service\entry_service();
    $entrytotalcount = $entryservice->count_history_for_student(
        $studentid, (int) $academicyear->id, $entryfilters, (int) $USER->id
    );
    $entries = $entryservice->get_history_for_student(
        $studentid, (int) $academicyear->id, $entryfilters, (int) $USER->id, $entrypage * $entryperpage, $entryperpage
    );

    $modalityrepository = new \local_monlaututoria\repository\modality_repository();
    $allmodalities = $modalityrepository->get_all(true);
    $modalityoptionsmap = [];
    foreach ($allmodalities as $modality) {
        $modalityoptionsmap[(int) $modality->id] = format_string($modality->name);
    }

    $entrytutorids = array_unique(array_map(static fn ($entry) => $entry->tutorid, $entries));
    $entrytutors = !empty($entrytutorids)
        ? $DB->get_records_list('user', 'id', $entrytutorids, '', 'id, firstname, lastname, email')
        : [];

    $reasonlinkrepository = new \local_monlaututoria\repository\entry_reason_repository();
    $entryids = array_map(static fn ($entry) => $entry->id, $entries);
    $reasonsbyentry = $islimitedview ? [] : $reasonlinkrepository->get_for_entries($entryids);
    $allreasonids = array_unique(array_merge(...array_values($reasonsbyentry ?: [[]])));
    $reasonrepository = new \local_monlaututoria\repository\reason_repository();
    $allreasons = !empty($allreasonids) ? $reasonrepository->get_many($allreasonids) : [];

    $entryfilterurlparams = array_filter([
        'id' => $studentid, 'tab' => 'tutorias', 'academicyearid' => $requestedacademicyearid ?: null,
    ]);
    $statusfilterurl = new moodle_url('/local/monlaututoria/student/view.php', $entryfilterurlparams);
    echo $OUTPUT->single_select(
        $statusfilterurl,
        'entrystatus',
        ['' => get_string('choosedots')] + \local_monlaututoria\domain\entry_status::get_options(),
        $statusfilter,
        [],
        'entrystatusselector'
    );
    echo $OUTPUT->single_select(
        $statusfilterurl,
        'modalityid',
        [0 => get_string('choosedots')] + $modalityoptionsmap,
        $modalityfilter,
        [],
        'entrymodalityselector'
    );
    if (!$islimitedview) {
        $reasonoptionsmap = [];
        foreach ($reasonrepository->get_all(true) as $reason) {
            $reasonoptionsmap[(int) $reason->id] = format_string($reason->name);
        }
        echo $OUTPUT->single_select(
            $statusfilterurl,
            'reasonid',
            [0 => get_string('choosedots')] + $reasonoptionsmap,
            $reasonfilter,
            [],
            'entryreasonselector'
        );
        echo $OUTPUT->single_select(
            $statusfilterurl,
            'visibilitytier',
            ['' => get_string('choosedots')] + [
                'contentvisible'  => get_string('entry_field_contentvisible', 'local_monlaututoria'),
                'noteinternal'    => get_string('entry_field_noteinternal', 'local_monlaututoria'),
                'noterestricted'  => get_string('entry_field_noterestricted', 'local_monlaututoria'),
            ],
            $visibilityfilter,
            [],
            'entryvisibilityselector'
        );
    }

    echo $renderer->entry_history_table($entries, $entrytutors, $allmodalities, $reasonsbyentry, $allreasons, $islimitedview);

    echo $OUTPUT->paging_bar($entrytotalcount, $entrypage, $entryperpage, $PAGE->url);
} else {
    // 'acuerdos': empty until phase 6.
    echo $OUTPUT->notification(
        get_string('studenttab_agreements_empty', 'local_monlaututoria'),
        \core\output\notification::NOTIFY_INFO
    );
}

echo $OUTPUT->footer();
