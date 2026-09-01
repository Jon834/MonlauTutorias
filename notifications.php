<?php
// This file is part of Moodle - http://moodle.org/

require(__DIR__ . '/../../config.php');

require_login();
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$context = context_system::instance();
\local_monlaututoria\feature::require_enabled(\local_monlaututoria\feature::NOTIFICATIONS);
$url = new moodle_url('/local/monlaututoria/notifications.php');
$service = new \local_monlaututoria\service\notification_preference_service();

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('notification_preferences_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('notification_preferences_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

if (optional_param('save', 0, PARAM_BOOL) && confirm_sesskey()) {
    $service->save_settings((int) $USER->id, [
        \local_monlaututoria\service\notification_preference_service::ASSIGNMENT_CHANGES => optional_param('assignmentchanges', 0, PARAM_BOOL),
        \local_monlaututoria\service\notification_preference_service::REFERRAL_CHANGES => optional_param('referralchanges', 0, PARAM_BOOL),
        \local_monlaututoria\service\notification_preference_service::FOLLOWUP_REMINDERS => optional_param('followupreminders', 0, PARAM_BOOL),
        \local_monlaututoria\service\notification_preference_service::AGREEMENT_REMINDERS => optional_param('agreementreminders', 0, PARAM_BOOL),
        \local_monlaututoria\service\notification_preference_service::DIGEST_FREQUENCY => optional_param('digestfrequency', 'daily', PARAM_ALPHA),
    ]);
    redirect($url, get_string('notification_preferences_saved', 'local_monlaututoria'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$settings = $service->get_settings((int) $USER->id);
$frequencyoptions = [
    'none' => get_string('notification_frequency_none', 'local_monlaututoria'),
    'daily' => get_string('notification_frequency_daily', 'local_monlaututoria'),
    'weekly' => get_string('notification_frequency_weekly', 'local_monlaututoria'),
];

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_monlaututoria');
echo $renderer->plugin_navigation('notifications');
echo $renderer->page_header_card(
    get_string('notification_preferences_title', 'local_monlaututoria'),
    get_string('notifications_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/dashboard.php'),
    get_string('page_back_dashboard', 'local_monlaututoria'),
    [],
    get_string('pluginname', 'local_monlaututoria')
);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false)]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'save', 'value' => 1]);
echo html_writer::start_div('form-group');
echo html_writer::checkbox('assignmentchanges', 1, !empty($settings['assignmentchanges']), get_string('notification_pref_assignmentchanges', 'local_monlaututoria'));
echo html_writer::end_div();
echo html_writer::start_div('form-group');
echo html_writer::checkbox('referralchanges', 1, !empty($settings['referralchanges']), get_string('notification_pref_referralchanges', 'local_monlaututoria'));
echo html_writer::end_div();
echo html_writer::start_div('form-group');
echo html_writer::checkbox('followupreminders', 1, !empty($settings['followupreminders']), get_string('notification_pref_followupreminders', 'local_monlaututoria'));
echo html_writer::end_div();
echo html_writer::start_div('form-group');
echo html_writer::checkbox('agreementreminders', 1, !empty($settings['agreementreminders']), get_string('notification_pref_agreementreminders', 'local_monlaututoria'));
echo html_writer::end_div();
echo html_writer::start_div('form-group');
echo html_writer::tag('label', get_string('notification_pref_digestfrequency', 'local_monlaututoria'), ['for' => 'id_digestfrequency']);
echo html_writer::select($frequencyoptions, 'digestfrequency', $settings['digestfrequency'], false, ['id' => 'id_digestfrequency']);
echo html_writer::end_div();
echo html_writer::empty_tag('br');
echo html_writer::tag('button', get_string('notification_preferences_save', 'local_monlaututoria'), ['type' => 'submit', 'class' => 'btn btn-primary']);
echo html_writer::end_tag('form');
echo $OUTPUT->footer();


