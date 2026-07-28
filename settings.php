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
 * Adds the local_monlaututoria pages to Site administration.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig
    || has_capability('local/monlaututoria:viewconfiguration', context_system::instance())
    || has_any_capability(
        ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents'],
        context_system::instance()
    )) {
    $ADMIN->add('localplugins', new admin_category(
        'local_monlaututoria',
        get_string('pluginname', 'local_monlaututoria')
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_academicyears',
        get_string('academicyears', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/academicyears.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:manageacademicyears']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_reasons',
        get_string('reasons', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/reasons.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:managecatalogues']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_modalities',
        get_string('modalities', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/modalities.php'),
        ['local/monlaututoria:viewconfiguration', 'local/monlaututoria:managecatalogues']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_cohort_visibility',
        get_string('cohort_visibility_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/cohort_visibility.php'),
        ['local/monlaututoria:managecatalogues']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_dashboard',
        get_string('dashboard_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/dashboard.php'),
        ['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_assignments',
        get_string('assignments', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/assignments/index.php'),
        ['local/monlaututoria:viewallassignments', 'local/monlaututoria:viewownstudents']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_import',
        get_string('csv_import_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/assignments/import.php'),
        ['local/monlaututoria:importassignments']
    ));

    // Phase 6.4: referrals have no ficha tab (see referral_service's class
    // docblock) — coordination/orientation/management reach their queue from
    // here instead, same as the assignments listing above.
    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_referrals',
        get_string('referrals_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/referrals/index.php'),
        ['local/monlaututoria:managereferrals']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_coordination',
        get_string('coordination_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/coordination.php'),
        ['local/monlaututoria:viewcoordinationdashboard', 'local/monlaututoria:viewallassignments']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_coordination_scopes',
        get_string('coordination_scopes_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/coordination_scopes.php'),
        ['local/monlaututoria:managecoordinationscopes']
    ));

    $ADMIN->add('local_monlaututoria', new admin_externalpage(
        'local_monlaututoria_notifications',
        get_string('notification_preferences_title', 'local_monlaututoria'),
        new moodle_url('/local/monlaututoria/notifications.php'),
        [
            'local/monlaututoria:viewownstudents',
            'local/monlaututoria:viewallassignments',
            'local/monlaututoria:viewcoordinationdashboard',
            'local/monlaututoria:managereferrals'
        ]
    ));

    // Phase 5.5: first real setting this plugin has ever needed — every page
    // above is an admin_externalpage instead. "Ventana de edición
    // configurable" (docs/fases/phase-5.md) means exactly this: how long
    // after a tutoring entry is created it may still be edited without
    // giving a reason; entry_service::update() reads it via get_config(),
    // never hardcodes it.
    $settings = new admin_settingpage(
        'local_monlaututoria_settings',
        get_string('settings_entryeditwindow_title', 'local_monlaututoria'),
        'local/monlaututoria:managecatalogues'
    );
    $settings->add(new admin_setting_configduration(
        'local_monlaututoria/entryeditwindow',
        get_string('setting_entryeditwindow', 'local_monlaututoria'),
        get_string('setting_entryeditwindow_desc', 'local_monlaututoria'),
        3 * DAYSECS
    ));

    // Real-use feedback: the tutor dashboard's referrals/priority sections
    // were confusing — a derivación can already be mentioned in a tutoring
    // entry's own text, and "priority" is a derived heuristic, not something
    // a tutor sets. Turning either off only hides that rendering on
    // dashboard.php and the block; referral_service/dashboard_service keep
    // computing everything exactly as before (nothing is deleted), and
    // referrals/index.php + coordination.php are unaffected — this only
    // controls what a tutor sees on their own dashboard.
    $settings->add(new admin_setting_configcheckbox(
        'local_monlaututoria/dashboard_showreferrals',
        get_string('setting_dashboard_showreferrals', 'local_monlaututoria'),
        get_string('setting_dashboard_showreferrals_desc', 'local_monlaututoria'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_monlaututoria/dashboard_showpriority',
        get_string('setting_dashboard_showpriority', 'local_monlaututoria'),
        get_string('setting_dashboard_showpriority_desc', 'local_monlaututoria'),
        1
    ));

    $ADMIN->add('local_monlaututoria', $settings);
} else {
    $settings = null;
}

