<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'changes' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],
    'reminders' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],
    'digests' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_PERMITTED,
        ],
    ],
    // Fase 14 — "el orientador SOP ha registrado una tutoría del alumno X".
    // Only the in-Moodle bell: email is disallowed by design.
    'sopentryrecorded' => [
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email' => MESSAGE_DISALLOWED,
        ],
    ],
];
