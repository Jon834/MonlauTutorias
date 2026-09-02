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
 * Detail view of a single tutoring entry (phase 5.4). Same 2-layer security
 * pattern as assignments/view.php: local/monlaututoria:viewstudent as the
 * page-level gate, then scope_service — here delegated to
 * entry_service::get_for_viewer(), which also applies the per-field content
 * masking (contentvisible/noteinternal/noterestricted) before this page ever
 * sees the row.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();

$id = required_param('id', PARAM_INT);

$entryrepository = new \local_monlaututoria\repository\entry_repository();
$rawentry = $entryrepository->get($id);
$isself = ((int) $USER->id === (int) $rawentry->studentid);
$canviewownfile = $isself && has_capability('local/monlaututoria:viewownfile', $context);
// Fase 13 — the viewstudent capability is not the only way in: a
// tutor-by-assignment (simple mode) or a former tutor of this student reach
// the entry too. get_for_viewer() below re-checks scope AND, for a former
// tutor, refuses any entry they did not record themselves.
$scopeservice = new \local_monlaututoria\service\scope_service();
if (!$canviewownfile
    && !$scopeservice->can_user_access_student((int) $USER->id, (int) $rawentry->studentid)) {
    require_capability('local/monlaututoria:viewstudent', $context);
}

$entryservice = new \local_monlaututoria\service\entry_service();
// Re-validates scope and applies the content masking — the raw record read
// above is only used for the isself check; every value shown below comes
// from this call, never from $rawentry directly.
$entry = $entryservice->get_for_viewer($id, (int) $USER->id);

$student = core_user::get_user($entry->studentid);
$tutor = core_user::get_user($entry->tutorid);
$academicyear = (new \local_monlaututoria\repository\academic_year_repository())->get($entry->academicyearid);

$PAGE->set_context($context);
$PAGE->set_url('/local/monlaututoria/entries/view.php', ['id' => $id]);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('entry_detail_title', 'local_monlaututoria'));
$PAGE->set_heading(get_string('entry_detail_title', 'local_monlaututoria'));
$PAGE->requires->css(new moodle_url('/local/monlaututoria/styles.css'));

$modalitylabel = '—';
if ($entry->modalityid !== null) {
    $modality = (new \local_monlaututoria\repository\modality_repository())->get($entry->modalityid);
    $modalitylabel = format_string($modality->name);
}

$reasonids = (new \local_monlaututoria\repository\entry_reason_repository())->get_for_entry($id);
$reasonnames = [];
if (!empty($reasonids) && !$isself) {
    // Same as the history table: reason categorisation is administrative,
    // kept out of the student's own limited view.
    $reasonrepository = new \local_monlaututoria\repository\reason_repository();
    foreach ($reasonrepository->get_many($reasonids) as $reason) {
        $reasonnames[] = format_string($reason->name);
    }
}

$participants = [];
if (!$isself) {
    $participanttypeoptions = \local_monlaututoria\domain\entry_participant_type::get_options();
    foreach ((new \local_monlaututoria\repository\entry_participant_repository())->get_for_entry($id) as $participant) {
        $name = $participant->userid !== null
            ? (($user = core_user::get_user((int) $participant->userid)) ? fullname($user) : '#' . $participant->userid)
            : $participant->externalname;
        $participants[] = [
            // Mustache's {{ }} already escapes on render (see the template) —
            // no manual s() here, or the output would be double-escaped.
            'typelabel' => $participanttypeoptions[$participant->participanttype] ?? $participant->participanttype,
            'name'      => $name,
        ];
    }
}

$dateformat = get_string('strftimedatefullshort', 'langconfig');
$statusoptions = \local_monlaututoria\domain\entry_status::get_options();

// Based on $rawentry->status, not $entry->status: mask_content() never
// touches status, so both are identical here — using the raw value just
// keeps this check independent of get_for_viewer()'s masking concerns,
// which are only about content, not about the entry's own lifecycle.
$isactive = $rawentry->status === \local_monlaututoria\domain\entry_status::ACTIVE;
$isowner = ((int) $rawentry->createdby === (int) $USER->id);
// Fase 13 — mirror entries/edit.php: in simple mode a current tutor may edit
// their own entry without the editownentry capability (a former tutor can't).
$caneditownsimple = \local_monlaututoria\feature::simple_mode()
    && !$scopeservice->access_is_historical_only((int) $USER->id, (int) $rawentry->studentid);
$canedit = $isactive
    && (has_capability('local/monlaututoria:editanyentry', $context)
        || ($isowner && (has_capability('local/monlaututoria:editownentry', $context) || $caneditownsimple)));
$canannul = $isactive && has_capability('local/monlaututoria:annulentry', $context);

$issop = ($entry->entrykind ?? 'regular') === \local_monlaututoria\domain\entry_kind::SOP;

$data = (object) [
    'studentname'          => fullname($student),
    'studentfichaurl'      => (new moodle_url('/local/monlaututoria/student/view.php', ['id' => $entry->studentid]))->out(false),
    'tutorname'            => $tutor ? fullname($tutor) : '#' . $entry->tutorid,
    'academicyearname'     => format_string($academicyear->name),
    'entrydateformatted'   => userdate($entry->entrydate, $dateformat),
    'modalitylabel'        => $modalitylabel,
    'statuslabel'          => $statusoptions[$entry->status] ?? $entry->status,
    'hasreasons'           => !empty($reasonnames),
    'reasonnames'          => implode(', ', $reasonnames),
    'contentvisible'       => $entry->contentvisible !== null ? $entry->contentvisible : '—',
    // Fase 14 — SOP entries: the student never reaches this page (get_for_viewer
    // throws), so anyone here is staff and sees the SOP recommendation.
    'issop'                => $issop,
    'recommendationsop'    => $entry->recommendationsop !== null ? $entry->recommendationsop : '—',
    'shownoteinternal'     => !$isself
        && (\local_monlaututoria\feature::simple_mode()
            || has_capability('local/monlaututoria:viewinternalnotes', $context)),
    'noteinternal'         => $entry->noteinternal !== null ? $entry->noteinternal : '—',
    'shownoterestricted'   => !$isself && has_capability('local/monlaututoria:viewrestrictednotes', $context)
        && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::RESTRICTEDNOTES),
    'noterestricted'       => $entry->noterestricted !== null ? $entry->noterestricted : '—',
    'hasnextfollowup'      => $entry->nextfollowupdate !== null,
    'nextfollowupformatted' => $entry->nextfollowupdate !== null ? userdate($entry->nextfollowupdate, $dateformat) : '',
    'hasparticipants'      => !empty($participants),
    'participants'         => $participants,
    'canedit'              => $canedit,
    'editurl'              => (new moodle_url('/local/monlaututoria/entries/edit.php', ['id' => $id]))->out(false),
    'canannul'             => $canannul,
    'annulurl'             => (new moodle_url('/local/monlaututoria/entries/annul.php', ['id' => $id]))->out(false),
    // Staff-only, same hard floor as entry_attachment_service — see its
    // class docblock for why there is no student-visible attachment tier.
    // Fase 13: the "!feature::enabled(...)" clauses hide these actions in
    // simple mode (their target pages refuse to load there anyway).
    'canseeattachments'    => !$isself && (
        ($issop && \local_monlaututoria\feature::simple_mode())
        || (has_capability('local/monlaututoria:viewinternalnotes', $context)
            && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::ATTACHMENTS))
    ),
    'attachmentsurl'       => (new moodle_url('/local/monlaututoria/entries/attachments.php', ['id' => $id]))->out(false),
    // Phase 6.1: staff-only, same reasoning as attachments — an agreement is
    // created by tutoring staff, never by the student themselves.
    'cancreateagreement'   => !$isself && has_capability('local/monlaututoria:createagreement', $context)
        && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::AGREEMENTS),
    'createagreementurl'   => (new moodle_url('/local/monlaututoria/agreements/create.php', ['entryid' => $id]))->out(false),
    // Phase 6.2: same reasoning as agreements.
    'cancreatefollowup'    => !$isself && has_capability('local/monlaututoria:createfollowup', $context)
        && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::FOLLOWUPS),
    'createfollowupurl'    => (new moodle_url('/local/monlaututoria/followups/create.php', ['entryid' => $id]))->out(false),
    // Phase 6.4: same reasoning as agreements/follow-ups.
    'cancreatereferral'    => !$isself && has_capability('local/monlaututoria:createreferral', $context)
        && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::REFERRALS),
    'createreferralurl'    => (new moodle_url('/local/monlaututoria/referrals/create.php', ['entryid' => $id]))->out(false),
];

// Fase 14 — show the attachments inline here (with download links), not only
// as a link to entries/attachments.php.
if ($data->canseeattachments) {
    $categorylabels = \local_monlaututoria\domain\entry_attachment_category::get_options()
        + \local_monlaututoria\domain\entry_attachment_category::get_sop_options();
    $attachmentlist = [];
    $attachmentservice = new \local_monlaututoria\service\entry_attachment_service($entryrepository);
    foreach ($attachmentservice->get_for_entry($id, (int) $USER->id) as [$file, $metadata]) {
        $attachmentlist[] = [
            'name'          => $file->get_filename(),
            'url'           => moodle_url::make_pluginfile_url(
                $context->id,
                'local_monlaututoria',
                \local_monlaututoria\service\entry_attachment_service::FILEAREA,
                $id,
                $file->get_filepath(),
                $file->get_filename(),
                true
            )->out(false),
            'categorylabel' => $metadata !== null ? ($categorylabels[$metadata->category] ?? $metadata->category) : '',
        ];
    }
    $data->hasattachmentlist = !empty($attachmentlist);
    $data->attachmentlist = $attachmentlist;
}

/** @var \local_monlaututoria\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_monlaututoria');

echo $OUTPUT->header();
echo $renderer->plugin_navigation('student', [
    'studentid' => (int) $entry->studentid,
    'studentlabel' => fullname($student),
    'academicyearid' => (int) $entry->academicyearid,
]);
echo $renderer->page_header_card(
    get_string('entry_detail_title', 'local_monlaututoria'),
    get_string('entry_detail_intro', 'local_monlaututoria'),
    new moodle_url('/local/monlaututoria/student/view.php', ['id' => $entry->studentid, 'tab' => 'tutorias', 'academicyearid' => $entry->academicyearid]),
    get_string('page_back_student_entries', 'local_monlaututoria'),
    [],
    fullname($student)
);
echo $renderer->entry_detail($data);
echo $OUTPUT->footer();


