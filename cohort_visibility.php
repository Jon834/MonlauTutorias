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
 * Global admin-curated allowlist of which Moodle cohorts this plugin treats
 * as relevant. Real-use feedback: every cohort dropdown in the plugin
 * (manual assignment creation, cohort-based bulk assignment, the implicit
 * scope a viewallassignments user gets) offered literally every cohort on
 * the site, including ones irrelevant to tutoring (e.g. staff groups).
 *
 * Distinct from coordination_scopes.php: that page restricts which of THESE
 * cohorts one specific coordinator may access; this one restricts the pool
 * for everyone. See cohort_visibility_service's class docblock for the
 * "empty means unrestricted" contract.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managecatalogues', $context);

$save = optional_param('save', 0, PARAM_BOOL);

$cohortrepository = new \local_monlaututoria\repository\cohort_repository();
$visibilityservice = new \local_monlaututoria\service\cohort_visibility_service();

if ($save && confirm_sesskey()) {
    $cohortids = optional_param_array('cohortids', [], PARAM_INT);
    $visibilityservice->replace_enabled_cohorts($cohortids, (int) $USER->id);
    redirect(
        new moodle_url('/local/monlaututoria/cohort_visibility.php'),
        get_string('cohort_visibility_saved', 'local_monlaututoria'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

admin_externalpage_setup('local_monlaututoria_cohort_visibility');
$PAGE->set_url('/local/monlaututoria/cohort_visibility.php');
$PAGE->set_title(get_string('cohort_visibility_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('cohort_visibility_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

$allcohorts = $cohortrepository->get_all();
$explicitlyenabled = $visibilityservice->get_explicitly_enabled_cohort_ids();
// An empty stored list means "unrestricted" (every cohort implicitly
// enabled) — reflect that as every checkbox pre-ticked, so the admin sees
// the current, real state rather than an all-unchecked list that would
// misleadingly suggest nothing is currently visible.
$currentlyenabled = !empty($explicitlyenabled) ? $explicitlyenabled : array_map('intval', array_keys($allcohorts));

echo $OUTPUT->header();
echo $renderer->plugin_navigation('configuration');
echo $renderer->page_header_card(
    get_string('cohort_visibility_title', 'local_monlaututoria'),
    get_string('cohort_visibility_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/index.php'),
    get_string('page_back_configuration', 'local_monlaututoria'),
    [],
    get_string('pluginname', 'local_monlaututoria')
);

if (empty($allcohorts)) {
    echo $OUTPUT->notification(get_string('cohort_visibility_empty', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);
} else {
    echo html_writer::start_tag('form', ['method' => 'post']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'save', 'value' => 1]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    foreach ($allcohorts as $cohort) {
        echo html_writer::div(
            html_writer::checkbox(
                'cohortids[]',
                (int) $cohort->id,
                in_array((int) $cohort->id, $currentlyenabled, true),
                format_string($cohort->name)
            ),
            'form-check'
        );
    }
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'class' => 'btn btn-primary mt-3',
        'value' => get_string('cohort_visibility_save', 'local_monlaututoria'),
    ]);
    echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
