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
 * Cohort-based bulk assignment: pick a cohort, tutor(s) and sync mode,
 * preview the classification, then confirm to write the real assignments.
 * The "confirm/apply" step cohort_assignment_preview_service's own docblock
 * names as phases 3C.3-3C.5 — same three-stage single-page pattern already
 * used by assignments/import.php for CSV import (form -> preview -> apply
 * result, all on this one URL, disambiguated by which fields were posted).
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::IMPORTS);
// A purpose-built capability, not assignstudents: created in phase 3C.1
// specifically for this feature (see db/access.php), left without a
// consumer until now. Grants access to the page and to PREVIEW_ONLY (which
// never writes) on its own — the other 3 modes each additionally require
// the capability of the write they can produce (see docs/seguridad-
// permisos.md's "Fase 3C.1" section, which already proposed this exact
// matrix before any page existed to enforce it).
require_capability('local/monlaututoria:managecohortassignments', $context);
$canoverridelock = has_capability('local/monlaututoria:overridelock', $context);
$canassignstudents = has_capability('local/monlaututoria:assignstudents', $context);
$canclosemissing = has_capability('local/monlaututoria:manageassignments', $context);
$canreplaceprimary = has_any_capability(
    ['local/monlaututoria:reassignstudents', 'local/monlaututoria:manageassignments'],
    $context
);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/assignments/cohort_create.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('cohort_assignment_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('cohort_assignment_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

// PREVIEW_ONLY is always offered — it never writes (cohort_assignment_apply_
// service refuses to ever apply it). The other 3 can all produce a
// CREATE_PRIMARY outcome (classify_student() creates one for any student
// with no current tutor regardless of mode, REPLACE_PRIMARY included), so
// assignstudents gates all of them; manageassignments/reassignstudents then
// additionally gate the higher-impact modes layered on top of that.
$modeoptions = [\local_monlaututoria\domain\cohort_sync_mode::PREVIEW_ONLY =>
    get_string('cohort_assignment_mode_preview_only', 'local_monlaututoria')];
if ($canassignstudents) {
    $modeoptions[\local_monlaututoria\domain\cohort_sync_mode::ADD_ONLY] =
        get_string('cohort_assignment_mode_add_only', 'local_monlaututoria');
    if ($canclosemissing) {
        $modeoptions[\local_monlaututoria\domain\cohort_sync_mode::ADD_AND_CLOSE_MISSING] =
            get_string('cohort_assignment_mode_add_and_close_missing', 'local_monlaututoria');
    }
    if ($canreplaceprimary) {
        $modeoptions[\local_monlaututoria\domain\cohort_sync_mode::REPLACE_PRIMARY] =
            get_string('cohort_assignment_mode_replace_primary', 'local_monlaututoria');
    }
}

// Only cohorts an admin has enabled for this plugin (cohort_visibility.php)
// — defaults to every Moodle cohort until an admin curates a subset.
$cohortoptions = [];
foreach ((new \local_monlaututoria\service\cohort_visibility_service())->get_visible_cohorts() as $cohort) {
    $cohortoptions[(int) $cohort->id] = format_string($cohort->name);
}

$academicyearrepository = new \local_monlaututoria\repository\academic_year_repository();
$academicyearoptions = [];
foreach ($academicyearrepository->get_all() as $year) {
    $academicyearoptions[(int) $year->id] = format_string($year->name);
}

$previewservice = new \local_monlaututoria\service\cohort_assignment_preview_service();
$applyservice = new \local_monlaututoria\service\cohort_assignment_apply_service();

$createform = new \local_monlaututoria\form\cohort_assignment_create_form(null, [
    'cohorts'       => $cohortoptions,
    'academicyears' => $academicyearoptions,
    'modeoptions'   => $modeoptions,
]);

$preview = null;
$previewedmode = null;
$applyresult = null;
$applyformforerrors = null;

$postedapplyuuid = optional_param('applyoperationuuid', '', PARAM_ALPHANUMEXT);

if ($postedapplyuuid !== '') {
    require_sesskey();

    $applyform = new \local_monlaututoria\form\cohort_assignment_apply_form();
    if (($applydata = $applyform->get_data()) !== null) {
        $applyresult = $applyservice->apply($postedapplyuuid, (int) $USER->id);
    } else {
        $applyformforerrors = $applyform;
    }
} else if (($createdata = $createform->get_data()) !== null) {
    // Mode restrictions are resolved here from the current user's
    // capabilities, then trusted by the service — same pattern as
    // canoverridelock throughout this plugin. A tampered POST cannot smuggle
    // in a mode this $modeoptions build above never actually offered.
    if (!isset($modeoptions[$createdata->mode])) {
        throw new \moodle_exception('error_cohort_mode_invalid', 'local_monlaututoria');
    }

    $command = new \local_monlaututoria\domain\cohort_assignment_command(
        (int) $createdata->cohortid,
        (int) $createdata->academicyearid,
        (int) $createdata->primarytutorid,
        $createdata->mode,
        !empty($createdata->cotutorid) ? (int) $createdata->cotutorid : null,
        !empty($createdata->timestart) ? (int) $createdata->timestart : null,
        !empty($createdata->timeend) ? (int) $createdata->timeend : null,
        !empty($createdata->includesuspended),
        !empty($createdata->allowsuspendedtutor),
        $canoverridelock
    );

    $preview = $previewservice->preview($command, (int) $USER->id);
    $previewedmode = $createdata->mode;
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('assignments');
echo $renderer->page_header_card(
    get_string('cohort_assignment_title', 'local_monlaututoria'),
    get_string('cohort_assignment_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/assignments/index.php'),
    get_string('page_back_assignments', 'local_monlaututoria'),
    [],
    get_string('pluginname', 'local_monlaututoria')
);

if ($applyresult !== null) {
    echo $OUTPUT->heading(get_string('cohort_assignment_apply_result_title', 'local_monlaututoria'), 3);
    echo html_writer::alist([
        get_string('cohort_assignment_apply_created', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\cohort_assignment_action::CREATE_PRIMARY
        )),
        get_string('cohort_assignment_apply_reassigned', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\cohort_assignment_action::REASSIGN_PRIMARY
        )),
        get_string('cohort_assignment_apply_closed', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\cohort_assignment_action::CLOSE_MISSING
        )),
        get_string('cohort_assignment_apply_nochange', 'local_monlaututoria', $applyresult->count(
            \local_monlaututoria\domain\cohort_assignment_action::NO_CHANGE
        )),
        get_string('cohort_assignment_apply_skipped', 'local_monlaututoria',
            $applyresult->count(\local_monlaututoria\domain\cohort_assignment_action::SKIP_EXISTING)
            + $applyresult->count(\local_monlaututoria\domain\cohort_assignment_action::SKIP_SUSPENDED)
            + $applyresult->count(\local_monlaututoria\domain\cohort_assignment_action::SKIP_INVALID)
            + $applyresult->count(\local_monlaututoria\domain\cohort_assignment_action::CONFLICT_PRIMARY)),
    ]);
    echo $OUTPUT->notification(
        get_string('cohort_assignment_apply_success', 'local_monlaututoria'),
        \core\output\notification::NOTIFY_SUCCESS
    );
    echo $renderer->cohort_assignment_apply_result_table($applyresult->rows);
} else if ($applyformforerrors !== null) {
    echo $OUTPUT->heading(get_string('cohort_assignment_apply_title', 'local_monlaututoria'), 3);
    $applyformforerrors->display();
} else if ($preview === null) {
    echo $OUTPUT->box(get_string('cohort_assignment_intro', 'local_monlaututoria'));
    $createform->display();
} else {
    echo $OUTPUT->heading(get_string('cohort_assignment_preview_summary_title', 'local_monlaututoria'), 3);
    echo html_writer::alist([
        get_string('cohort_assignment_summary_total', 'local_monlaututoria', $preview->summary->totalmembers),
        get_string('cohort_assignment_summary_tocreate', 'local_monlaututoria', $preview->summary->tocreatecount),
        get_string('cohort_assignment_summary_toreassign', 'local_monlaututoria', $preview->summary->toreassigncount),
        get_string('cohort_assignment_summary_tocreatecotutor', 'local_monlaututoria', $preview->summary->tocreatecotutorcount),
        get_string('cohort_assignment_summary_toclose', 'local_monlaututoria', $preview->summary->toclosecount),
        get_string('cohort_assignment_summary_nochange', 'local_monlaututoria', $preview->summary->nochangecount),
        get_string('cohort_assignment_summary_skipped', 'local_monlaututoria', $preview->summary->skippedcount),
        get_string('cohort_assignment_summary_suspended', 'local_monlaututoria', $preview->summary->suspendedcount),
        get_string('cohort_assignment_summary_conflicts', 'local_monlaututoria', $preview->summary->conflictcount),
    ]);

    echo $renderer->cohort_assignment_preview_table($preview->items);

    if ($preview->summary->conflictcount > 0) {
        echo $OUTPUT->notification(
            get_string('cohort_assignment_conflicts_warning', 'local_monlaututoria'),
            \core\output\notification::NOTIFY_WARNING
        );
    }

    // cohort_sync_mode::PREVIEW_ONLY never has a confirm step — apply() itself
    // refuses it (see cohort_assignment_apply_service), so there is nothing
    // useful this button could do here.
    if ($previewedmode !== \local_monlaututoria\domain\cohort_sync_mode::PREVIEW_ONLY) {
        echo $OUTPUT->heading(get_string('cohort_assignment_apply_title', 'local_monlaututoria'), 3);
        echo $OUTPUT->box(get_string('cohort_assignment_apply_intro', 'local_monlaututoria'));
        $applyform = new \local_monlaututoria\form\cohort_assignment_apply_form();
        $applyform->set_data((object) ['applyoperationuuid' => $preview->operationuuid]);
        $applyform->display();
    }
}

echo $OUTPUT->footer();
