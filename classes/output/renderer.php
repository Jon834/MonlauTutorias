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

namespace local_monlaututoria\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Renders the simple admin list tables for academic years and catalogues.
 * Plain html_writer tables are used rather than Mustache templates: these are
 * internal admin listings, not learner-facing UI, so the extra templating
 * layer would add indirection without benefit at this scope.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class renderer extends \plugin_renderer_base {

    /**
     * @param \stdClass[] $years
     * @param bool $canmanage
     * @param bool $canoverridelock
     * @return string
     */
    public function academic_years_list(array $years, bool $canmanage, bool $canoverridelock): string {
        if (empty($years)) {
            return $this->output->notification(
                get_string('academicyear_list_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $table = new \html_table();
        $table->head = [
            get_string('academicyear_name', 'local_monlaututoria'),
            get_string('academicyear_shortname', 'local_monlaututoria'),
            get_string('academicyear_startdate', 'local_monlaututoria'),
            get_string('academicyear_enddate', 'local_monlaututoria'),
            get_string('academicyear_active', 'local_monlaututoria'),
            get_string('academicyear_locked', 'local_monlaututoria'),
            '',
        ];

        foreach ($years as $year) {
            $table->data[] = [
                format_string($year->name),
                format_string($year->shortname),
                userdate($year->startdate, get_string('strftimedatefullshort', 'langconfig')),
                userdate($year->enddate, get_string('strftimedatefullshort', 'langconfig')),
                !empty($year->active) ? get_string('yes') : get_string('no'),
                !empty($year->locked) ? get_string('yes') : get_string('no'),
                $canmanage ? $this->academic_year_actions($year, $canoverridelock) : '',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \stdClass $year
     * @param bool $canoverridelock
     * @return string
     */
    private function academic_year_actions(\stdClass $year, bool $canoverridelock): string {
        $actions = [];
        $editable = empty($year->locked) || $canoverridelock;

        if ($editable) {
            $editurl = new \moodle_url('/local/monlaututoria/academicyear_edit.php', ['id' => $year->id]);
            $actions[] = \html_writer::link($editurl, get_string('academicyear_edit', 'local_monlaututoria'));

            if (empty($year->active)) {
                $activateurl = new \moodle_url(
                    '/local/monlaututoria/academicyear_activate.php',
                    ['id' => $year->id]
                );
                $actions[] = \html_writer::link($activateurl, get_string('academicyear_activate', 'local_monlaututoria'));
            }
        }

        if (empty($year->locked) || $canoverridelock) {
            $lockurl = new \moodle_url('/local/monlaututoria/academicyear_lock.php', [
                'id'      => $year->id,
                'lock'    => empty($year->locked) ? 1 : 0,
                'sesskey' => sesskey(),
            ]);
            $lockstring = empty($year->locked) ? 'academicyear_lock' : 'academicyear_unlock';
            $actions[] = \html_writer::link($lockurl, get_string($lockstring, 'local_monlaututoria'));
        }

        if (empty($year->active) && empty($year->locked)) {
            $deleteurl = new \moodle_url('/local/monlaututoria/academicyear_delete.php', ['id' => $year->id]);
            $actions[] = \html_writer::link($deleteurl, get_string('academicyear_delete', 'local_monlaututoria'));
        }

        return implode(' | ', $actions);
    }

    /**
     * @param \stdClass[] $items
     * @param string $type 'reason' or 'modality'
     * @param bool $canmanage
     * @return string
     */
    public function catalogue_list(array $items, string $type, bool $canmanage): string {
        $emptystring = $type . '_list_empty';
        if (empty($items)) {
            return $this->output->notification(
                get_string($emptystring, 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $table = new \html_table();
        $head = [
            get_string($type . '_name', 'local_monlaututoria'),
            get_string($type . '_shortname', 'local_monlaututoria'),
            get_string($type . '_active', 'local_monlaututoria'),
        ];
        if ($type === 'reason') {
            $head[] = get_string('reason_requiresfollowup', 'local_monlaututoria');
            $head[] = get_string('reason_defaultvisibility', 'local_monlaututoria');
        }
        $head[] = '';
        $table->head = $head;

        foreach ($items as $item) {
            $row = [
                format_string($item->name),
                format_string($item->shortname),
                !empty($item->active) ? get_string('yes') : get_string('no'),
            ];

            if ($type === 'reason') {
                $row[] = !empty($item->requiresfollowup) ? get_string('yes') : get_string('no');
                $row[] = \local_monlaututoria\domain\visibility_level::get_options()[(int) $item->defaultvisibility] ?? '';
            }

            $row[] = $canmanage ? $this->catalogue_actions($item, $type) : '';
            $table->data[] = $row;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \stdClass $item
     * @param string $type
     * @return string
     */
    private function catalogue_actions(\stdClass $item, string $type): string {
        $editpage = $type === 'reason' ? 'reason_edit.php' : 'modality_edit.php';
        $actions = [];

        $editurl = new \moodle_url('/local/monlaututoria/' . $editpage, ['id' => $item->id]);
        $actions[] = \html_writer::link($editurl, get_string($type . '_edit', 'local_monlaututoria'));

        $activatestring = !empty($item->active) ? $type . '_deactivate' : $type . '_activate';
        $activateurl = new \moodle_url('/local/monlaututoria/catalogue_action.php', [
            'type'    => $type,
            'id'      => $item->id,
            'action'  => !empty($item->active) ? 'deactivate' : 'activate',
            'sesskey' => sesskey(),
        ]);
        $actions[] = \html_writer::link($activateurl, get_string($activatestring, 'local_monlaututoria'));

        $upurl = new \moodle_url('/local/monlaututoria/catalogue_action.php', [
            'type' => $type, 'id' => $item->id, 'action' => 'moveup', 'sesskey' => sesskey(),
        ]);
        $actions[] = \html_writer::link($upurl, get_string($type . '_moveup', 'local_monlaututoria'));

        $downurl = new \moodle_url('/local/monlaututoria/catalogue_action.php', [
            'type' => $type, 'id' => $item->id, 'action' => 'movedown', 'sesskey' => sesskey(),
        ]);
        $actions[] = \html_writer::link($downurl, get_string($type . '_movedown', 'local_monlaututoria'));

        $deleteurl = new \moodle_url('/local/monlaututoria/catalogue_action.php', [
            'type' => $type, 'id' => $item->id, 'action' => 'delete', 'sesskey' => sesskey(),
        ]);
        $actions[] = $this->output->action_link(
            $deleteurl,
            get_string($type . '_delete', 'local_monlaututoria'),
            new \confirm_action(get_string($type . '_delete_confirm', 'local_monlaututoria', format_string($item->name)))
        );

        return implode(' | ', $actions);
    }

    /**
     * @return string
     */
    public function noactiveacademicyear_warning(): string {
        return $this->output->notification(
            get_string('noactiveacademicyear_warning', 'local_monlaututoria'),
            \core\output\notification::NOTIFY_WARNING
        );
    }

    /**
     * Computes the display data for a status badge, distinguishing "upcoming"
     * (active but timestart in the future) from the 4 stored status values.
     * Shared by the list, detail and history templates via the
     * assignment_status partial — never colour-only, always label + icon.
     *
     * @param string $status one of assignment_status::values()
     * @param int $timestart
     * @return array
     */
    public function status_badge_data(string $status, int $timestart): array {
        if ($status === \local_monlaututoria\domain\assignment_status::ACTIVE && $timestart > time()) {
            return [
                'status'      => 'upcoming',
                'statuslabel' => get_string('assignment_upcoming', 'local_monlaututoria'),
                'statusclass' => 'info',
                'statusicon'  => 'clock-o',
            ];
        }

        $map = [
            \local_monlaututoria\domain\assignment_status::ACTIVE    => ['success', 'check-circle'],
            \local_monlaututoria\domain\assignment_status::CLOSED    => ['secondary', 'times-circle'],
            \local_monlaututoria\domain\assignment_status::CANCELLED => ['danger', 'ban'],
            \local_monlaututoria\domain\assignment_status::PENDING   => ['warning', 'hourglass-half'],
        ];
        [$class, $icon] = $map[$status] ?? ['secondary', 'question-circle'];

        return [
            'status'      => $status,
            'statuslabel' => get_string('assignmentstatus_' . $status, 'local_monlaututoria'),
            'statusclass' => $class,
            'statusicon'  => $icon,
        ];
    }

    /**
     * @param array $rows each row already merged with display data (student/tutor
     *                    names, cohort/academic year names, status badge data, urls)
     * @return string
     */
    public function assignments_list(array $rows): string {
        $data = [
            'hasrows' => !empty($rows),
            'rows'    => array_values($rows),
            'message' => get_string('assignments_list_empty', 'local_monlaututoria'),
        ];

        return $this->render_from_template('local_monlaututoria/assignments_list', $data);
    }

    /**
     * @param \stdClass $data already merged with display data (see assignments/view.php)
     * @return string
     */
    public function assignment_detail(\stdClass $data): string {
        return $this->render_from_template('local_monlaututoria/assignment_detail', (array) $data);
    }

    /**
     * Renders the "cabecera y resumen" of a student's longitudinal file
     * (phase 4.1).
     *
     * @param \local_monlaututoria\domain\student_summary $summary
     * @param \stdClass $academicyear
     * @param \stdClass $student
     * @param bool $islimitedview phase 4.3: true when the viewer is the
     *                            student themselves — suppresses the links
     *                            out to assignments/view.php, which they have
     *                            no capability to open
     * @return string
     */
    public function student_summary(
        \local_monlaututoria\domain\student_summary $summary,
        \stdClass $academicyear,
        \stdClass $student,
        bool $islimitedview = false
    ): string {
        global $DB;

        $showlinks = !$islimitedview;

        $dateformat = get_string('strftimedatefullshort', 'langconfig');

        $cohortname = '—';
        if ($summary->primaryassignment !== null && !empty($summary->primaryassignment->cohortid)) {
            $cohort = $DB->get_record('cohort', ['id' => $summary->primaryassignment->cohortid]);
            if ($cohort) {
                $cohortname = format_string($cohort->name);
            }
        }

        // Batch-fetch every tutor referenced below in one query instead of
        // calling core_user::get_user() per row — that call is never cached
        // (plain $DB->get_record() for any id other than noreply/support), so
        // doing it in a loop is the same N+1 shape already fixed elsewhere in
        // this project (assignments/index.php, phase 3E.4). Even though the
        // count here is small (primary + cotutors + last + upcoming), the
        // fix costs nothing and keeps the pattern consistent (phase 4.4).
        $tutorids = [];
        if ($summary->primaryassignment !== null) {
            $tutorids[] = (int) $summary->primaryassignment->tutorid;
        }
        foreach ($summary->cotutorassignments as $row) {
            $tutorids[] = (int) $row->tutorid;
        }
        if ($summary->lastassignment !== null) {
            $tutorids[] = (int) $summary->lastassignment->tutorid;
        }
        foreach ($summary->upcomingassignments as $row) {
            $tutorids[] = (int) $row->tutorid;
        }
        $tutors = !empty($tutorids) ? $DB->get_records_list('user', 'id', array_unique($tutorids)) : [];

        $primarytutorname = '—';
        $primaryassignmenturl = null;
        if ($summary->primaryassignment !== null) {
            $tutor = $tutors[(int) $summary->primaryassignment->tutorid] ?? null;
            $primarytutorname = $tutor ? fullname($tutor) : '#' . $summary->primaryassignment->tutorid;
            $primaryassignmenturl = (new \moodle_url(
                '/local/monlaututoria/assignments/view.php',
                ['id' => $summary->primaryassignment->id]
            ))->out(false);
        }

        $cotutors = [];
        foreach ($summary->cotutorassignments as $row) {
            $tutor = $tutors[(int) $row->tutorid] ?? null;
            $cotutors[] = [
                'name' => $tutor ? fullname($tutor) : '#' . $row->tutorid,
                'url'  => (new \moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $row->id]))->out(false),
            ];
        }

        $last = null;
        if ($summary->lastassignment !== null) {
            $row = $summary->lastassignment;
            $tutor = $tutors[(int) $row->tutorid] ?? null;
            $last = [
                'tutorname'          => $tutor ? fullname($tutor) : '#' . $row->tutorid,
                'statuslabel'        => get_string('assignmentstatus_' . $row->status, 'local_monlaututoria'),
                'timestartformatted' => userdate($row->timestart, $dateformat),
                'url'                => (new \moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $row->id]))->out(false),
            ];
        }

        $upcoming = [];
        foreach ($summary->upcomingassignments as $row) {
            $tutor = $tutors[(int) $row->tutorid] ?? null;
            $upcoming[] = [
                'tutorname'          => $tutor ? fullname($tutor) : '#' . $row->tutorid,
                'timestartformatted' => userdate($row->timestart, $dateformat),
                'url'                => (new \moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $row->id]))->out(false),
            ];
        }

        $data = [
            'studentname'       => fullname($student),
            'academicyearname'  => format_string($academicyear->name),
            'cohortname'        => $cohortname,
            'showlinks'         => $showlinks,
            'haveprimary'       => $summary->primaryassignment !== null,
            'primarytutorname'  => $primarytutorname,
            'primaryassignmenturl' => $primaryassignmenturl,
            'hascotutors'       => !empty($cotutors),
            'cotutors'          => $cotutors,
            'haslast'           => $last !== null,
            'last'              => $last,
            'hasupcoming'       => !empty($upcoming),
            'upcoming'          => $upcoming,
        ];

        return $this->render_from_template('local_monlaututoria/student_summary', $data);
    }

    /**
     * Simple link-based tab bar for the student file (phase 4.1/4.2):
     * "resumen" and "historial" have real content; "tutorias"/"acuerdos"
     * stay empty until phases 5/6 (see docs/fases/phase-4.md, "Pestañas
     * iniciales") but are listed here too, since the phase spec asks for
     * them to exist as tabs from the start, not to appear once populated.
     *
     * @param string $active one of 'resumen', 'historial', 'tutorias', 'acuerdos'
     * @param int $studentid
     * @param int|null $academicyearid carried over into every tab's URL so
     *                                 switching tabs keeps the selected year
     * @return string
     */
    public function student_tabs(string $active, int $studentid, ?int $academicyearid): string {
        $tabs = [
            'resumen'   => get_string('studenttab_summary', 'local_monlaututoria'),
            'historial' => get_string('studenttab_history', 'local_monlaututoria'),
            'tutorias'  => get_string('studenttab_tutoring', 'local_monlaututoria'),
            'acuerdos'  => get_string('studenttab_agreements', 'local_monlaututoria'),
        ];

        $links = '';
        foreach ($tabs as $key => $label) {
            $params = ['id' => $studentid, 'tab' => $key];
            if ($academicyearid !== null) {
                $params['academicyearid'] = $academicyearid;
            }
            $url = new \moodle_url('/local/monlaututoria/student/view.php', $params);
            $classes = 'nav-link' . ($key === $active ? ' active' : '');
            // These are real page links, not a JS-toggled ARIA tablist, so
            // aria-current="page" (not aria-selected) is the correct signal
            // for screen readers — same pattern as a breadcrumb's current
            // item. Keyboard access itself already works natively: each tab
            // is a plain <a href>, reachable and activatable with Tab/Enter
            // without any extra wiring.
            $attributes = ['class' => $classes];
            if ($key === $active) {
                $attributes['aria-current'] = 'page';
            }
            $links .= \html_writer::link($url, $label, $attributes);
        }

        return \html_writer::div($links, 'nav nav-tabs mb-3');
    }

    /**
     * Renders the "historial de asignaciones" tab of the student file (phase
     * 4.2): a chronological table (most recent academic year and start date
     * first), showing whichever motive applies to a row — the closing reason
     * when it was closed, or the reassignment reason when it was created by
     * reassign_primary_tutor() — never both, a row is only ever one or the
     * other. Plain html_writer table, same rationale as the other admin
     * listings in this class.
     *
     * @param \stdClass[] $rows raw local_tut_assignment records
     * @param array<int, \stdClass> $tutors keyed by tutorid
     * @param array<int, \stdClass> $academicyears keyed by academicyearid
     * @param bool $islimitedview phase 4.3: true when the viewer is the
     *                            student themselves — drops the "motivo" and
     *                            "origen" columns (internal administrative
     *                            categorisation, not meant for the student)
     *                            and the link out to assignments/view.php
     *                            (which they have no capability to open)
     * @return string
     */
    public function student_history_table(array $rows, array $tutors, array $academicyears, bool $islimitedview = false): string {
        if (empty($rows)) {
            return $this->output->notification(
                get_string('student_summary_no_assignments', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $typeoptions = \local_monlaututoria\domain\assignment_type::get_options();
        $sourceoptions = \local_monlaututoria\domain\assignment_source::get_options();
        $closereasonoptions = \local_monlaututoria\domain\assignment_close_reason::get_options();
        $reassignreasonoptions = \local_monlaututoria\domain\assignment_reassign_reason::get_options();

        $table = new \html_table();
        $table->head = [
            get_string('assignment_col_academicyear', 'local_monlaututoria'),
            get_string('assignment_col_tutor', 'local_monlaututoria'),
            get_string('assignment_col_type', 'local_monlaututoria'),
            get_string('assignment_col_status', 'local_monlaututoria'),
            get_string('assignment_col_timestart', 'local_monlaututoria'),
            get_string('assignment_col_timeend', 'local_monlaututoria'),
        ];
        if (!$islimitedview) {
            $table->head[] = get_string('assignment_col_source', 'local_monlaututoria');
            $table->head[] = get_string('student_history_col_reason', 'local_monlaututoria');
            $table->head[] = '';
        }

        foreach ($rows as $row) {
            $tutor = $tutors[(int) $row->tutorid] ?? null;
            $academicyear = $academicyears[(int) $row->academicyearid] ?? null;
            $badge = $this->status_badge_data($row->status, (int) $row->timestart);

            $cells = [
                $academicyear ? format_string($academicyear->name) : '—',
                // html_writer::table() never auto-escapes cell content the
                // way Mustache does — unlike fullname() used elsewhere in
                // this class inside render_from_template() contexts,
                // fullname() here needs an explicit s(), since a tutor's
                // profile name is real user-controlled data, not a fixed enum
                // label (phase 4.2 XSS check, same standard as 3E.2).
                $tutor ? s(fullname($tutor)) : '#' . $row->tutorid,
                $typeoptions[$row->assignmenttype] ?? $row->assignmenttype,
                \html_writer::span($badge['statuslabel'], 'badge badge-' . $badge['statusclass']),
                userdate($row->timestart, $dateformat),
                !empty($row->timeend) ? userdate($row->timeend, $dateformat) : '—',
            ];

            if (!$islimitedview) {
                $reason = '—';
                if (!empty($row->closereason)) {
                    $reason = $closereasonoptions[$row->closereason] ?? $row->closereason;
                } else if (!empty($row->reassignreason)) {
                    $reason = $reassignreasonoptions[$row->reassignreason] ?? $row->reassignreason;
                }

                $cells[] = $sourceoptions[$row->source] ?? $row->source;
                $cells[] = $reason;
                $cells[] = \html_writer::link(
                    new \moodle_url('/local/monlaututoria/assignments/view.php', ['id' => $row->id]),
                    get_string('assignment_viewdetail', 'local_monlaututoria')
                );
            }

            $table->data[] = $cells;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param array $entries each already merged with display data, most recent first
     * @return string
     */
    public function assignment_history(array $entries): string {
        $data = [
            'hasentries' => !empty($entries),
            'entries'    => array_values($entries),
            'message'    => get_string('assignment_history_empty', 'local_monlaututoria'),
        ];

        return $this->render_from_template('local_monlaututoria/assignment_history', $data);
    }

    /**
     * Renders the CSV import preview table (phase 3D.2). Plain html_writer
     * table, same rationale as the other admin listings in this class: this
     * is an internal admin screen, not learner-facing UI.
     *
     * @param \local_monlaututoria\domain\csv_import_preview_row[] $rows
     * @return string
     */
    public function csv_import_preview_table(array $rows): string {
        if (empty($rows)) {
            return $this->output->notification(
                get_string('csv_preview_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $table = new \html_table();
        $table->head = [
            get_string('csv_col_row', 'local_monlaututoria'),
            get_string('csv_col_status', 'local_monlaututoria'),
            get_string('assignment_col_student', 'local_monlaututoria'),
            get_string('assignment_col_tutor', 'local_monlaututoria'),
            get_string('assignment_col_academicyear', 'local_monlaututoria'),
            get_string('assignment_col_cohort', 'local_monlaututoria'),
            get_string('csv_col_messages', 'local_monlaututoria'),
        ];

        foreach ($rows as $row) {
            $table->data[] = [
                $row->rownumber,
                $this->csv_row_status_badge($row->status),
                s($row->values['student'] ?? ''),
                s($row->values['tutor'] ?? ''),
                s($row->values['academicyear'] ?? ''),
                s($row->values['cohort'] ?? '') ?: '—',
                $this->csv_row_messages($row->messagecodes),
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param string $status one of csv_import_row_status::values()
     * @return string
     */
    private function csv_row_status_badge(string $status): string {
        $map = [
            \local_monlaututoria\domain\csv_import_row_status::VALID    => ['success', 'csv_status_valid'],
            \local_monlaututoria\domain\csv_import_row_status::WARNING  => ['warning', 'csv_status_warning'],
            \local_monlaututoria\domain\csv_import_row_status::CONFLICT => ['danger', 'csv_status_conflict'],
            \local_monlaututoria\domain\csv_import_row_status::ERROR    => ['danger', 'csv_status_error'],
            \local_monlaututoria\domain\csv_import_row_status::EXCLUDED => ['secondary', 'csv_status_excluded'],
        ];
        [$class, $stringkey] = $map[$status] ?? ['secondary', $status];

        return \html_writer::span(get_string($stringkey, 'local_monlaututoria'), 'badge badge-' . $class);
    }

    /**
     * Renders the per-row result of an applied CSV import (phase 3D.4): same
     * rationale as csv_import_preview_table, plain html_writer table on an
     * internal admin screen.
     *
     * @param \local_monlaututoria\domain\csv_import_apply_result_row[] $rows
     * @return string
     */
    public function csv_import_apply_result_table(array $rows): string {
        if (empty($rows)) {
            return $this->output->notification(
                get_string('csv_apply_result_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $table = new \html_table();
        $table->head = [
            get_string('csv_col_row', 'local_monlaututoria'),
            get_string('csv_col_outcome', 'local_monlaututoria'),
            get_string('assignment_col_student', 'local_monlaututoria'),
            get_string('assignment_col_tutor', 'local_monlaututoria'),
            get_string('csv_col_messages', 'local_monlaututoria'),
        ];

        foreach ($rows as $row) {
            $table->data[] = [
                $row->rownumber,
                $this->csv_apply_outcome_badge($row->outcome),
                s($row->values['student'] ?? ''),
                s($row->values['tutor'] ?? ''),
                $row->errormessagecode !== null
                    ? get_string($row->errormessagecode, 'local_monlaututoria')
                    : '—',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param string $outcome one of csv_import_row_outcome::values()
     * @return string
     */
    private function csv_apply_outcome_badge(string $outcome): string {
        $map = [
            \local_monlaututoria\domain\csv_import_row_outcome::CREATED         => ['success', 'csv_apply_outcome_created'],
            \local_monlaututoria\domain\csv_import_row_outcome::REASSIGNED      => ['success', 'csv_apply_outcome_reassigned'],
            \local_monlaututoria\domain\csv_import_row_outcome::NO_CHANGE       => ['secondary', 'csv_apply_outcome_no_change'],
            \local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_CONFLICT => ['warning', 'csv_apply_outcome_skipped_conflict'],
            \local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_ERROR   => ['warning', 'csv_apply_outcome_skipped_error'],
            \local_monlaututoria\domain\csv_import_row_outcome::SKIPPED_EXCLUDED => ['secondary', 'csv_apply_outcome_skipped_excluded'],
            \local_monlaututoria\domain\csv_import_row_outcome::FAILED          => ['danger', 'csv_apply_outcome_failed'],
        ];
        [$class, $stringkey] = $map[$outcome] ?? ['secondary', $outcome];

        return \html_writer::span(get_string($stringkey, 'local_monlaututoria'), 'badge badge-' . $class);
    }

    /**
     * @param string[] $codes csv_import_error_code and/or csv_import_message_code values
     * @return string
     */
    private function csv_row_messages(array $codes): string {
        if (empty($codes)) {
            return '—';
        }

        $labels = array_map(
            static fn (string $code) => get_string('csv_message_' . $code, 'local_monlaututoria'),
            $codes
        );

        return implode('; ', $labels);
    }
}
