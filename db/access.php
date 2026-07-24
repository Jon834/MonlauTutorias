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
 * Capability definitions for local_monlaututoria.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/monlaututoria:view' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewconfiguration' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:manageacademicyears' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:managecatalogues' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:overridelock' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewownstudents' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewstudent' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewownfile' => [
        // Defaults to the 'user' (authenticated user) archetype, not
        // 'student': this capability is defined at CONTEXT_SYSTEM, and
        // Moodle's Student role is normally assigned at course context, never
        // system context — a system-level archetype default for 'student'
        // would silently never apply in a typical installation. 'user' IS
        // assigned at system context for every logged-in account by default,
        // which is what actually makes "a student can view their own file
        // out of the box" true. This is safe to grant broadly: scope_service
        // only ever uses it to let $userid see $userid's own record, never
        // anyone else's, regardless of who else also holds it.
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => ['user' => CAP_ALLOW],
    ],
    'local/monlaututoria:viewhistoricalassignments' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:assignstudents' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:manageassignments' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:managecohortassignments' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:importassignments' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:reassignstudents' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewallassignments' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:manageclosedassignments' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],

    // Phase 5.1 — tutoring entries. Only the 3 read capabilities that
    // entry_service::get_for_viewer() already checks are defined here;
    // createentry/editownentry/editanyentry/annulentry (phase 5.2/5.3/5.5)
    // are deferred to whichever increment adds the page that enforces them,
    // same criterion already applied throughout this plugin (e.g.
    // assignstudents was defined in 3B.2, not earlier in 3A when only the
    // service existed).
    'local/monlaututoria:viewstudentvisiblecontent' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewinternalnotes' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:viewrestrictednotes' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],

    // Phase 5.2 — quick registration page now exists to enforce this.
    'local/monlaututoria:createentry' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],

    // Phase 5.5 — edition/annulment pages now exist to enforce these.
    'local/monlaututoria:editownentry' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:editanyentry' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
    'local/monlaututoria:annulentry' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
];
