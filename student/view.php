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
 * Student longitudinal file. All 4 tabs have real content as of phase 6.1:
 * "Resumen" (4.1), "Historial" (4.2), "Tutorías" (5.4) and "Acuerdos" (6.1)
 * (see "Pestañas iniciales" in docs/fases/phase-4.md for the original
 * placeholder plan).
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
if (!in_array($tab, ['resumen', 'historial', 'tutorias', 'acuerdos', 'seguimientos'], true)) {
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
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('student', [
    'studentid' => $studentid,
    'studentlabel' => fullname($student),
    'academicyearid' => $academicyear !== null ? (int) $academicyear->id : null,
]);
echo $renderer->page_header_card(
    get_string('student_summary_title', 'local_monlaututoria'),
    get_string('student_detail_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/assignments/index.php'),
    get_string('page_back_assignments', 'local_monlaututoria'),
    [],
    fullname($student)
);
echo $renderer->contextual_help(
    get_string('help_studentview_title', 'local_monlaututoria'),
    get_string('help_studentview_body', 'local_monlaututoria')
);
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

$academicyearselecthtml = '';
if (!empty($academicyearoptions)) {
    $selectorurl = new moodle_url('/local/monlaututoria/student/view.php', ['id' => $studentid, 'tab' => $tab]);
    $academicyearselect = new single_select(
        $selectorurl,
        'academicyearid',
        $academicyearoptions,
        $academicyear !== null ? (int) $academicyear->id : '',
        ['' => get_string('choosedots')],
        'academicyearselector'
    );
    $academicyearselect->set_label(get_string('filter_academicyear', 'local_monlaututoria'));
    $academicyearselecthtml = $OUTPUT->render($academicyearselect);
}

// Photo and academic year selector previously ran straight into each other
// (echoed one after another with no container) — now a proper row, with the
// selector's own visible label instead of single_select's screen-reader-only
// default.
echo html_writer::div(
    $OUTPUT->user_picture($student, ['size' => 100]) . $academicyearselecthtml,
    'd-flex flex-wrap align-items-center gap-3 mb-4'
);

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
        // mb-4 (not the smaller mb-2 gap-only spacing used before): this row
        // sits directly above the filter toolbar below, and the two were
        // reported as visually running into each other with no clear
        // separation.
        echo html_writer::div(
            $OUTPUT->single_button(
                new moodle_url('/local/monlaututoria/entries/create.php', $entryurlparams),
                get_string('entry_register', 'local_monlaututoria')
            ) .
            $OUTPUT->single_button(
                new moodle_url('/local/monlaututoria/entries/create_full.php', $entryurlparams),
                get_string('entry_full_register', 'local_monlaututoria')
            ),
            'd-flex flex-wrap gap-2 mb-4'
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

    $entrysort = optional_param('entrysort', '', PARAM_ALPHA);
    if (!in_array($entrysort, \local_monlaututoria\repository\entry_repository::sortable_columns(), true)) {
        $entrysort = 'entrydate';
    }
    $entrydir = strtoupper(optional_param('entrydir', 'DESC', PARAM_ALPHA)) === 'ASC' ? 'ASC' : 'DESC';

    $entryservice = new \local_monlaututoria\service\entry_service();
    $entrytotalcount = $entryservice->count_history_for_student(
        $studentid, (int) $academicyear->id, $entryfilters, (int) $USER->id
    );
    $entries = $entryservice->get_history_for_student(
        $studentid, (int) $academicyear->id, $entryfilters, (int) $USER->id,
        $entrypage * $entryperpage, $entryperpage, $entrysort, $entrydir
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

    // Each select gets a visible label (single_select's own label is
    // screen-reader-only by default) and the whole row shares the
    // .local-monlaututoria-toolbar wrapper (flex-wrap + gap + margin,
    // already defined in styles.css) — previously these were echoed bare,
    // one after another with no container, which is what made the filter
    // row look cramped directly under the buttons above.
    $entrystatusselect = new single_select(
        $statusfilterurl, 'entrystatus',
        ['' => get_string('choosedots')] + \local_monlaututoria\domain\entry_status::get_options(),
        $statusfilter, [], 'entrystatusselector'
    );
    $entrystatusselect->set_label(get_string('filter_status', 'local_monlaututoria'));

    $entrymodalityselect = new single_select(
        $statusfilterurl, 'modalityid',
        [0 => get_string('choosedots')] + $modalityoptionsmap,
        $modalityfilter, [], 'entrymodalityselector'
    );
    $entrymodalityselect->set_label(get_string('entry_field_modality', 'local_monlaututoria'));

    $toolbar = $OUTPUT->render($entrystatusselect) . $OUTPUT->render($entrymodalityselect);

    if (!$islimitedview) {
        $reasonoptionsmap = [];
        foreach ($reasonrepository->get_all(true) as $reason) {
            $reasonoptionsmap[(int) $reason->id] = format_string($reason->name);
        }
        $entryreasonselect = new single_select(
            $statusfilterurl, 'reasonid',
            [0 => get_string('choosedots')] + $reasonoptionsmap,
            $reasonfilter, [], 'entryreasonselector'
        );
        $entryreasonselect->set_label(get_string('entry_field_reason', 'local_monlaututoria'));

        $entryvisibilityselect = new single_select(
            $statusfilterurl, 'visibilitytier',
            ['' => get_string('choosedots')] + [
                'contentvisible'  => get_string('entry_field_contentvisible', 'local_monlaututoria'),
                'noteinternal'    => get_string('entry_field_noteinternal', 'local_monlaututoria'),
                'noterestricted'  => get_string('entry_field_noterestricted', 'local_monlaututoria'),
            ],
            $visibilityfilter, [], 'entryvisibilityselector'
        );
        $entryvisibilityselect->set_label(get_string('entry_field_visibilitytier', 'local_monlaututoria'));

        $toolbar .= $OUTPUT->render($entryreasonselect) . $OUTPUT->render($entryvisibilityselect);
    }

    echo html_writer::div($toolbar, 'local-monlaututoria-toolbar');

    $entrysortbaseurl = new moodle_url('/local/monlaututoria/student/view.php', $entryfilterurlparams + array_filter([
        'entrystatus' => $statusfilter ?: null,
        'modalityid' => $modalityfilter ?: null,
        'reasonid' => $reasonfilter ?: null,
        'visibilitytier' => $visibilityfilter ?: null,
    ], static fn ($value) => $value !== null));
    echo $renderer->entry_history_table(
        $entries, $entrytutors, $allmodalities, $reasonsbyentry, $allreasons, $islimitedview,
        $entrysort, $entrydir, $entrysortbaseurl
    );

    echo $OUTPUT->paging_bar($entrytotalcount, $entrypage, $entryperpage, $PAGE->url);
} else if ($tab === 'acuerdos') {
    // 'acuerdos' (phase 6.1/6.3): real listing, filterable by "vencidos".
    $overduefilter = optional_param('agreementoverdue', 0, PARAM_BOOL);
    $agreementfilters = $overduefilter ? ['overdue' => true] : [];

    $agreementservice = new \local_monlaututoria\service\agreement_service();
    $agreements = $agreementservice->list_for_student(
        $studentid, (int) $academicyear->id, $agreementfilters, (int) $USER->id
    );

    $responsibleuserids = array_filter(array_map(
        static fn ($agreement) => $agreement->responsibleuserid, $agreements
    ));
    $responsibleusers = !empty($responsibleuserids)
        ? $DB->get_records_list('user', 'id', array_unique($responsibleuserids), '', 'id, firstname, lastname, email')
        : [];

    $canmanageagreements = !$islimitedview && has_capability('local/monlaututoria:manageagreements', $context);

    if (!$islimitedview) {
        // There is no "create a standalone agreement" flow (it always
        // originates from a specific tutoring entry, see agreements/create.php's
        // docblock) — this tab only offers the "vencidos" filter, creation
        // happens from entries/view.php's own "Crear acuerdo" link.
        $overdueurl = new moodle_url('/local/monlaututoria/student/view.php', array_filter([
            'id' => $studentid, 'tab' => 'acuerdos', 'academicyearid' => $requestedacademicyearid ?: null,
        ]));
        echo $OUTPUT->single_select(
            $overdueurl,
            'agreementoverdue',
            [0 => get_string('choosedots'), 1 => get_string('agreements_filter_overdue', 'local_monlaututoria')],
            $overduefilter ? 1 : 0,
            [],
            'agreementoverdueselector'
        );
    }

    echo $renderer->agreements_table($agreements, $responsibleusers, $canmanageagreements);
} else {
    // 'seguimientos' (phase 6.2/6.3): staff-only, no student-visible tier —
    // see followup_service's class docblock. The tab link itself is still
    // shown to the student (student_tabs() renders all 5 unconditionally,
    // same as the other tabs), but its content is never queried for them.
    if ($islimitedview) {
        echo $OUTPUT->notification(
            get_string('followups_empty', 'local_monlaututoria'),
            \core\output\notification::NOTIFY_INFO
        );
    } else {
        $followupoverduefilter = optional_param('followupoverdue', 0, PARAM_BOOL);
        $followupfilters = $followupoverduefilter ? ['overdue' => true] : [];

        $followupservice = new \local_monlaututoria\service\followup_service();
        $followups = $followupservice->list_for_student(
            $studentid, (int) $academicyear->id, $followupfilters, (int) $USER->id
        );

        $canmanagefollowups = has_capability('local/monlaututoria:managefollowups', $context);

        $followupoverdueurl = new moodle_url('/local/monlaututoria/student/view.php', array_filter([
            'id' => $studentid, 'tab' => 'seguimientos', 'academicyearid' => $requestedacademicyearid ?: null,
        ]));
        echo $OUTPUT->single_select(
            $followupoverdueurl,
            'followupoverdue',
            [0 => get_string('choosedots'), 1 => get_string('followups_filter_overdue', 'local_monlaututoria')],
            $followupoverduefilter ? 1 : 0,
            [],
            'followupoverdueselector'
        );

        echo $renderer->followups_table($followups, $canmanagefollowups);
    }
}

echo $OUTPUT->footer();





