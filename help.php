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
 * Static explanatory page: what a tutoring entry, agreement, follow-up and
 * referral are, and who can see what. Purely informational — no student
 * data of any kind is read or shown here, so no scope check is needed
 * beyond being logged in, same as notifications.php.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/help.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('help_page_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('help_page_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

// Fase 13 — in simple mode there are no agreements/follow-ups/referrals, so
// the help page is just "what a tutoring entry is" and "what the student
// sees", with wording that does not mention the hidden concepts.
$simplemode = \local_monlaututoria\feature::simple_mode();
$concepts = $simplemode ? ['entry'] : ['entry', 'agreement', 'followup', 'referral'];
$shortsuffix = $simplemode ? '_short_simple' : '_short';
$fullsuffix = $simplemode ? '_full_simple' : '_full';

echo $OUTPUT->header();
echo $renderer->plugin_navigation('help');
echo $renderer->page_header_card(
    get_string('help_page_title', 'local_monlaututoria'),
    get_string($simplemode ? 'help_page_intro_simple' : 'help_page_intro', 'local_monlaututoria'),
    null,
    null,
    [],
    get_string('pluginname', 'local_monlaututoria')
);

$html = html_writer::start_div('local-monlaututoria-help-index');
foreach ($concepts as $concept) {
    $html .= html_writer::start_div('local-monlaututoria-help-index__card');
    $html .= html_writer::tag('h3', get_string("help_concept_{$concept}_title", 'local_monlaututoria'));
    $html .= html_writer::tag('p', get_string("help_concept_{$concept}{$shortsuffix}", 'local_monlaututoria'));
    $html .= html_writer::tag('p', get_string("help_concept_{$concept}{$fullsuffix}", 'local_monlaututoria'), ['class' => 'text-muted']);
    $html .= html_writer::end_div();
}
$html .= html_writer::end_div();
echo $html;

echo html_writer::tag('h3', get_string('help_visibility_title', 'local_monlaututoria'));
echo html_writer::tag('p', get_string($simplemode ? 'help_visibility_body_simple' : 'help_visibility_body', 'local_monlaututoria'));

echo $OUTPUT->footer();
