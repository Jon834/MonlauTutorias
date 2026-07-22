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
 * Navigation hub for local_monlaututoria administration.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$viewcaps = [
    'local/monlaututoria:viewconfiguration',
    'local/monlaututoria:manageacademicyears',
    'local/monlaututoria:managecatalogues',
];
if (!has_any_capability($viewcaps, $context)) {
    throw new required_capability_exception($context, 'local/monlaututoria:viewconfiguration', 'nopermissions', '');
}

$PAGE->set_url(new moodle_url('/local/monlaututoria/index.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_monlaututoria'));
$PAGE->set_heading(get_string('pluginname', 'local_monlaututoria'));

echo $OUTPUT->header();

$links = [
    ['url' => '/local/monlaututoria/academicyears.php', 'string' => 'academicyears'],
    ['url' => '/local/monlaututoria/reasons.php', 'string' => 'reasons'],
    ['url' => '/local/monlaututoria/modalities.php', 'string' => 'modalities'],
];

echo html_writer::start_tag('ul');
foreach ($links as $link) {
    echo html_writer::tag(
        'li',
        html_writer::link(new moodle_url($link['url']), get_string($link['string'], 'local_monlaututoria'))
    );
}
echo html_writer::end_tag('ul');

echo $OUTPUT->footer();
