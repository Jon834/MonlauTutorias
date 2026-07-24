<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\local_monlaututoria\event\assignment_created',
        'callback' => '\local_monlaututoria\observer\notification_observer::assignment_created',
    ],
    [
        'eventname' => '\local_monlaututoria\event\student_reassigned',
        'callback' => '\local_monlaututoria\observer\notification_observer::student_reassigned',
    ],
    [
        'eventname' => '\local_monlaututoria\event\referral_updated',
        'callback' => '\local_monlaututoria\observer\notification_observer::referral_updated',
    ],
];
