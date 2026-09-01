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
     * @param string $text
     * @return string
     */
    public function tooltip(string $text): string {
        return \html_writer::tag('span', '?', [
            'class' => 'local-monlaututoria-tooltip',
            'title' => $text,
            'aria-label' => $text,
            'tabindex' => '0',
        ]);
    }

    /**
     * A collapsed-by-default help disclosure ("¿Qué es...?"), using the
     * native HTML5 <details>/<summary> pair rather than a Bootstrap
     * JS-driven collapse — this plugin has no JavaScript of its own, and
     * <details> needs none: it works, and is keyboard-accessible, out of the
     * box in every browser this project targets. $body is built from static,
     * developer-authored lang strings (never user/DB content), so it is
     * inserted as trusted HTML, same convention as the rest of this renderer
     * for non-data strings.
     *
     * @param string $title
     * @param string $body one or more already-formed HTML fragments (e.g. <p>...</p>)
     * @return string
     */
    public function contextual_help(string $title, string $body): string {
        return \html_writer::tag(
            'details',
            \html_writer::tag('summary', s($title)) . \html_writer::div($body, 'local-monlaututoria-help__body'),
            ['class' => 'local-monlaututoria-help mb-3']
        );
    }

    /**
     * @param string $active
     * @param array $contextdata
     * @return string
     */
    public function plugin_navigation(string $active, array $contextdata = []): string {
        $systemcontext = \context_system::instance();
        $items = [];

        if (has_any_capability(['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments'], $systemcontext)) {
            $items[] = [
                'key' => 'dashboard',
                'label' => get_string('nav_dashboard', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/dashboard.php'),
                'title' => get_string('nav_dashboard_tip', 'local_monlaututoria'),
            ];
        }

        // Fase 13 — in simple mode "Asignaciones" belongs to coordination
        // only: a plain tutor manages their students from the panel and the
        // ficha, and does not create/close assignments. In full mode a tutor
        // with viewownstudents still sees it (scoped to their own students).
        $canseeassignments = \local_monlaututoria\feature::simple_mode()
            ? has_capability('local/monlaututoria:viewallassignments', $systemcontext)
            : has_any_capability(['local/monlaututoria:viewownstudents', 'local/monlaututoria:viewallassignments'], $systemcontext);
        if ($canseeassignments) {
            $items[] = [
                'key' => 'assignments',
                'label' => get_string('nav_assignments', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/assignments/index.php'),
                'title' => get_string('nav_assignments_tip', 'local_monlaututoria'),
            ];
        }

        if (has_capability('local/monlaututoria:managereferrals', $systemcontext)
            && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::REFERRALS)) {
            $items[] = [
                'key' => 'referrals',
                'label' => get_string('nav_referrals', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/referrals/index.php'),
                'title' => get_string('nav_referrals_tip', 'local_monlaututoria'),
            ];
        }

        if (has_any_capability(['local/monlaututoria:viewcoordinationdashboard', 'local/monlaututoria:viewallassignments'], $systemcontext)
            && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::COORDINATION)) {
            $items[] = [
                'key' => 'coordination',
                'label' => get_string('nav_coordination', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/coordination.php'),
                'title' => get_string('nav_coordination_tip', 'local_monlaututoria'),
            ];
        }

        if (has_capability('local/monlaututoria:managecoordinationscopes', $systemcontext)
            && \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::COORDINATION)) {
            $items[] = [
                'key' => 'coordinators',
                'label' => get_string('nav_coordinators', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/coordination_scopes.php'),
                'title' => get_string('nav_coordinators_tip', 'local_monlaututoria'),
            ];
        }

        if (isloggedin() && !isguestuser()) {
            if (\local_monlaututoria\feature::enabled(\local_monlaututoria\feature::NOTIFICATIONS)) {
                $items[] = [
                    'key' => 'notifications',
                    'label' => get_string('nav_notifications', 'local_monlaututoria'),
                    'url' => new \moodle_url('/local/monlaututoria/notifications.php'),
                    'title' => get_string('nav_notifications_tip', 'local_monlaututoria'),
                ];
            }
            // No capability check beyond being logged in: purely explanatory
            // content (what a tutoring entry/agreement/follow-up/referral
            // is), nothing here exposes any student's data.
            $items[] = [
                'key' => 'help',
                'label' => get_string('nav_help', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/help.php'),
                'title' => get_string('nav_help_tip', 'local_monlaututoria'),
            ];
        }

        if (has_any_capability([
            'local/monlaututoria:viewconfiguration',
            'local/monlaututoria:manageacademicyears',
            'local/monlaututoria:managecatalogues'
        ], $systemcontext)) {
            $items[] = [
                'key' => 'configuration',
                'label' => get_string('nav_configuration', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/index.php'),
                'title' => get_string('nav_configuration_tip', 'local_monlaututoria'),
            ];
        }

        if (!empty($contextdata['studentid'])) {
            $params = ['id' => (int) $contextdata['studentid']];
            if (!empty($contextdata['academicyearid'])) {
                $params['academicyearid'] = (int) $contextdata['academicyearid'];
            }
            $items[] = [
                'key' => 'student',
                'label' => !empty($contextdata['studentlabel']) ? $contextdata['studentlabel'] : get_string('nav_student', 'local_monlaututoria'),
                'url' => new \moodle_url('/local/monlaututoria/student/view.php', $params),
                'title' => get_string('nav_student_tip', 'local_monlaututoria'),
            ];
        }

        $links = [];
        foreach ($items as $item) {
            $classes = 'local-monlaututoria-nav__link';
            if ($item['key'] === $active) {
                $classes .= ' is-active';
            }
            $attributes = [
                'class' => $classes,
                'title' => $item['title'],
            ];
            if ($item['key'] === $active) {
                $attributes['aria-current'] = 'page';
            }
            $links[] = \html_writer::link($item['url'], s($item['label']), $attributes);
        }

        return \html_writer::div(implode('', $links), 'local-monlaututoria-nav mb-4');
    }

    /**
     * @param string $title
     * @param string $description
     * @param \moodle_url|null $backurl
     * @param string|null $backlabel
     * @param array $actions
     * @param string|null $eyebrow
     * @return string
     */
    public function page_header_card(
        string $title,
        string $description,
        ?\moodle_url $backurl = null,
        ?string $backlabel = null,
        array $actions = [],
        ?string $eyebrow = null
    ): string {
        $header = '';
        if ($eyebrow !== null && $eyebrow !== '') {
            $header .= \html_writer::div(s($eyebrow), 'local-monlaututoria-page-header__eyebrow');
        }
        $header .= \html_writer::tag('h1', s($title), ['class' => 'local-monlaututoria-page-header__title']);
        $header .= \html_writer::tag('p', s($description), ['class' => 'local-monlaututoria-page-header__description']);

        if ($backurl !== null) {
            $header .= \html_writer::link(
                $backurl,
                s($backlabel ?? get_string('back')),
                ['class' => 'local-monlaututoria-page-header__back', 'title' => $backlabel ?? get_string('back')]
            );
        }

        $actionlinks = [];
        foreach ($actions as $action) {
            if (empty($action['url']) || empty($action['label'])) {
                continue;
            }
            $actionlinks[] = \html_writer::link(
                $action['url'],
                s($action['label']),
                [
                    'class' => 'local-monlaututoria-page-header__action',
                    'title' => $action['title'] ?? $action['label'],
                ]
            );
        }

        $content = \html_writer::div($header, 'local-monlaututoria-page-header__main');
        if (!empty($actionlinks)) {
            $content .= \html_writer::div(implode('', $actionlinks), 'local-monlaututoria-page-header__actions');
        }

        return \html_writer::div($content, 'local-monlaututoria-page-header mb-4');
    }

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
     * @param \local_monlaututoria\domain\tutor_dashboard_summary $summary
     * @param bool $showreferrals site setting local_monlaututoria/dashboard_showreferrals —
     *                            the underlying count is always computed, this only
     *                            controls whether the card is rendered
     * @param bool $showpriority site setting local_monlaututoria/dashboard_showpriority, same idea
     * @return string
     */
    public function dashboard_summary_cards(
        \local_monlaututoria\domain\tutor_dashboard_summary $summary,
        bool $showreferrals = true,
        bool $showpriority = true
    ): string {
        // Fase 13 — the follow-ups/agreements/family-contact cards only make
        // sense when those modules are on. The counts are still computed by
        // dashboard_service; this just omits the tiles in simple mode.
        $showfollowups = \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::FOLLOWUPS);
        $showagreements = \local_monlaututoria\feature::enabled(\local_monlaututoria\feature::AGREEMENTS);

        $cards = [
            ['label' => get_string('dashboard_summary_assigned', 'local_monlaututoria'), 'value' => $summary->assignedcount],
            ['label' => get_string('dashboard_summary_attended', 'local_monlaututoria'), 'value' => $summary->attendedcount],
            ['label' => get_string('dashboard_summary_pendinginitial', 'local_monlaututoria'), 'value' => $summary->pendinginitialcount],
            ['label' => get_string('dashboard_summary_coverage', 'local_monlaututoria'), 'value' => format_float($summary->coveragepercent, 2) . ' %'],
        ];
        if ($showfollowups) {
            $cards[] = ['label' => get_string('dashboard_summary_followupsoverdue', 'local_monlaututoria'), 'value' => $summary->overduefollowupcount];
        }
        if ($showagreements) {
            $cards[] = ['label' => get_string('dashboard_summary_agreementspending', 'local_monlaututoria'), 'value' => $summary->pendingagreementcount + $summary->overdueagreementcount];
        }
        if ($showreferrals) {
            $cards[] = ['label' => get_string('dashboard_summary_referrals', 'local_monlaututoria'), 'value' => $summary->openreferralcount];
        }
        if ($showpriority) {
            $cards[] = ['label' => get_string('dashboard_summary_priority', 'local_monlaututoria'), 'value' => $summary->prioritystudentcount];
        }
        // Family-contact count stays in both modes: it just counts tutoring
        // entries that involved a family, useful to a tutor regardless of
        // whether families have their own login.
        $cards[] = ['label' => get_string('dashboard_summary_familycontacts', 'local_monlaututoria'), 'value' => $summary->familycontactcount];

        $html = '';
        foreach ($cards as $card) {
            $html .= \html_writer::div(
                \html_writer::div(s($card['value']), 'h3 mb-1')
                . \html_writer::div(s($card['label']), 'text-muted'),
                'local-monlaututoria-stat-card'
            );
        }

        return \html_writer::div($html, 'local-monlaututoria-dashboard-summary d-grid gap-3 mb-4');
    }

    /**
     * @param \local_monlaututoria\domain\tutor_dashboard_student[] $students
     * @param array<int, \stdClass> $studentusers keyed by student id
     * @param int $academicyearid
     * @param bool $cancreateentry
     * @param bool $cancreatefollowup
     * @param bool $showpriority site setting local_monlaututoria/dashboard_showpriority —
     *                           only controls whether the "Prioridad" column is rendered
     * @param string $currentsort 'studentname', 'lastentry', 'entrycount' or ''
     *                             — sorting the array itself is the caller's
     *                             job (dashboard.php), same reasoning as
     *                             $showpriority: this method only renders
     * @param string $currentdir 'ASC' or 'DESC'
     * @param \moodle_url $baseurl current page URL with every other filter
     *                             already on it except studentsort/studentdir
     * @return string
     */
    public function dashboard_students_table(
        array $students,
        array $studentusers,
        int $academicyearid,
        bool $cancreateentry,
        bool $cancreatefollowup,
        bool $showpriority,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl
    ): string {
        if (empty($students)) {
            return $this->output->notification(
                get_string('dashboard_students_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('assignment_col_student', 'local_monlaututoria'), 'studentname',
                $currentsort, $currentdir, $baseurl, 'studentsort', 'studentdir'
            ),
            $this->sortable_header(
                get_string('dashboard_col_lastentry', 'local_monlaututoria'), 'lastentry',
                $currentsort, $currentdir, $baseurl, 'studentsort', 'studentdir'
            ),
            $this->sortable_header(
                get_string('dashboard_col_entrycount', 'local_monlaututoria'), 'entrycount',
                $currentsort, $currentdir, $baseurl, 'studentsort', 'studentdir'
            ),
            get_string('dashboard_col_missinginitial', 'local_monlaututoria'),
            get_string('dashboard_col_coverage', 'local_monlaututoria'),
            get_string('dashboard_col_pendingbundle', 'local_monlaututoria'),
        ];
        if ($showpriority) {
            $table->head[] = get_string('dashboard_col_priority', 'local_monlaututoria');
        }
        $table->head[] = get_string('assignment_col_actions', 'local_monlaututoria');

        foreach ($students as $student) {
            $user = $studentusers[$student->studentid] ?? null;
            $studentname = $user ? fullname($user) : '#' . $student->studentid;
            $studenturl = new \moodle_url('/local/monlaututoria/student/view.php', [
                'id' => $student->studentid,
                'academicyearid' => $academicyearid,
            ]);
            $lastentry = $student->latestactiveentry !== null
                ? userdate((int) $student->latestactiveentry->entrydate, get_string('strftimedatefullshort', 'langconfig'))
                : '-';
            $missinginitial = $student->missinginitial ? get_string('yes') : get_string('no');
            $coveragestring = $student->covered
                ? get_string('dashboard_coveragestatus_covered', 'local_monlaututoria')
                : get_string('dashboard_coveragestatus_pending_initial', 'local_monlaututoria');
            $pendingbundle = 'Seg. ' . $student->openfollowupcount
                . ' / Acu. ' . $student->openagreementcount
                . ' / Der. ' . $student->openreferralcount;

            $actions = [
                \html_writer::link($studenturl, get_string('dashboard_action_viewstudent', 'local_monlaututoria')),
            ];
            if ($cancreateentry) {
                $actions[] = \html_writer::link(
                    new \moodle_url('/local/monlaututoria/entries/create.php', [
                        'studentid' => $student->studentid,
                        'academicyearid' => $academicyearid,
                    ]),
                    get_string('dashboard_action_createentry', 'local_monlaututoria')
                );
            }
            if ($cancreatefollowup && $student->latestactiveentry !== null) {
                $actions[] = \html_writer::link(
                    new \moodle_url('/local/monlaututoria/followups/create.php', [
                        'entryid' => $student->latestactiveentry->id,
                    ]),
                    get_string('dashboard_action_createfollowup', 'local_monlaututoria')
                );
            }

            $row = [
                \html_writer::link($studenturl, format_string($studentname)),
                $lastentry,
                $student->activeentrycount,
                $missinginitial,
                $coveragestring,
                s($pendingbundle),
            ];
            if ($showpriority) {
                $row[] = $student->ispriority ? get_string('yes') : get_string('no');
            }
            $row[] = implode(' | ', $actions);
            $table->data[] = $row;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * "Mis alumnos" roster (fase 13): a card grid with each current primary
     * student's photo and name, linking straight to their tutoring file
     * (student/view.php, "Tutorías" tab). Its point is face recognition — the
     * operational tables (coverage, pending work) live in the "Pendientes"
     * view of the same dashboard. Not sortable: it deliberately keeps the
     * caller's order (dashboard.php already sorts $students by name by
     * default). "Sin tutoría aún" vs "N tutorías" is spelled out in text, not
     * only signalled by colour (WCAG 2.2, same rule as the rest of this UI).
     *
     * @param \local_monlaututoria\domain\tutor_dashboard_student[] $students
     * @param array<int, \stdClass> $studentusers keyed by student id, each with
     *                              the fields user_picture() needs (see
     *                              \core_user\fields::for_userpic())
     * @param int $academicyearid carried into every card's URL
     * @return string
     */
    public function dashboard_student_roster(array $students, array $studentusers, int $academicyearid): string {
        if (empty($students)) {
            return $this->output->notification(
                get_string('dashboard_students_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $cards = '';

        foreach ($students as $student) {
            $user = $studentusers[$student->studentid] ?? null;
            $name = $user ? fullname($user) : '#' . $student->studentid;

            $url = new \moodle_url('/local/monlaututoria/student/view.php', [
                'id' => $student->studentid,
                'academicyearid' => $academicyearid,
                'tab' => 'tutorias',
            ]);

            $picture = $user
                ? $this->output->user_picture($user, ['size' => 72, 'link' => false, 'alttext' => false])
                : '';

            if ((int) $student->activeentrycount > 0) {
                $meta = \html_writer::span(
                    get_string('dashboard_roster_entrycount', 'local_monlaututoria', (int) $student->activeentrycount),
                    'local-monlaututoria-roster__meta'
                );
                if ($student->latestactiveentry !== null) {
                    $meta .= \html_writer::span(
                        get_string(
                            'dashboard_roster_lastentry',
                            'local_monlaututoria',
                            userdate((int) $student->latestactiveentry->entrydate, $dateformat)
                        ),
                        'local-monlaututoria-roster__last'
                    );
                }
            } else {
                $meta = \html_writer::span(
                    $this->output->pix_icon('i/warning', '') . ' '
                        . get_string('dashboard_roster_noentry', 'local_monlaututoria'),
                    'local-monlaututoria-roster__meta is-pending'
                );
            }

            $cards .= \html_writer::link(
                $url,
                \html_writer::div($picture, 'local-monlaututoria-roster__photo')
                    . \html_writer::div(s($name), 'local-monlaututoria-roster__name')
                    . \html_writer::div($meta, 'local-monlaututoria-roster__metawrap'),
                ['class' => 'local-monlaututoria-roster__card']
            );
        }

        return \html_writer::div($cards, 'local-monlaututoria-roster');
    }

    /**
     * A clickable column header that toggles sort direction — click once for
     * ascending, again for descending; clicking a different column always
     * starts ascending. Shared by every sortable table in this plugin
     * instead of duplicating the same toggle-and-arrow logic per table.
     * Purely a rendering helper: the actual sort (SQL ORDER BY, or a PHP
     * usort() for the tables built from an already-fully-loaded array) is
     * each page's own responsibility — this only builds the link and the
     * ▲/▼ indicator for whichever column is currently active.
     *
     * @param string $label
     * @param string $column stable key sent back as the "sort" URL param —
     *                        the page must validate it against its own
     *                        whitelist before using it, same as any other
     *                        user-supplied param
     * @param string $currentsort the column currently active, '' for none
     * @param string $currentdir 'ASC' or 'DESC' — only meaningful when
     *                            $currentsort === $column
     * @param \moodle_url $baseurl already carries every other current
     *                             filter/page param; this only adds sort/dir
     * @param string $sortparam URL param name for the sort column — defaults
     *                           to 'sort', but a page with more than one
     *                           independently-sortable table on it (e.g.
     *                           dashboard.php) must give each table its own
     *                           name so their sort states do not collide
     * @param string $dirparam URL param name for the sort direction, same
     *                         reasoning as $sortparam
     * @return string
     */
    public function sortable_header(
        string $label,
        string $column,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl,
        string $sortparam = 'sort',
        string $dirparam = 'dir'
    ): string {
        $isactive = $currentsort === $column;
        $newdir = ($isactive && strtoupper($currentdir) === 'ASC') ? 'DESC' : 'ASC';

        $url = new \moodle_url($baseurl, [$sortparam => $column, $dirparam => $newdir]);
        $suffix = '';
        if ($isactive) {
            $suffix = ' ' . (strtoupper($currentdir) === 'ASC' ? '▲' : '▼');
        }

        return \html_writer::link($url, s($label) . $suffix);
    }

    /**
     * A muted, non-boxed line for "nothing to show here" states that are
     * common and expected (an empty dashboard section, one half of a
     * split overdue/upcoming pair) — as opposed to $this->output->notification()'s
     * bright NOTIFY_INFO box, which is the right amount of emphasis for a
     * standalone empty listing but reads as an alarming wall of colour when
     * several of these sit one after another on the same dashboard.
     *
     * @param string $message
     * @return string
     */
    private function subtle_empty_hint(string $message): string {
        return \html_writer::tag('p', s($message), ['class' => 'text-muted small mb-3']);
    }

    /**
     * @param \local_monlaututoria\domain\followup[] $followups
     * @param array<int, \stdClass> $students keyed by student id
     * @param bool $canmanage
     * @param string $currentsort 'studentname', 'duedate', 'priority' or ''
     * @param string $currentdir 'ASC' or 'DESC'
     * @param \moodle_url $baseurl current page URL with every other filter
     *                             already on it except followupsort/followupdir
     * @return string
     */
    public function dashboard_followups_table(
        array $followups,
        array $students,
        bool $canmanage,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl
    ): string {
        if (empty($followups)) {
            return $this->subtle_empty_hint(get_string('dashboard_followups_empty', 'local_monlaututoria'));
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $priorityoptions = \local_monlaututoria\domain\priority_level::get_options();
        $statusoptions = \local_monlaututoria\domain\followup_status::get_options();

        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('assignment_col_student', 'local_monlaututoria'), 'studentname',
                $currentsort, $currentdir, $baseurl, 'followupsort', 'followupdir'
            ),
            $this->sortable_header(
                get_string('followup_field_duedate', 'local_monlaututoria'), 'duedate',
                $currentsort, $currentdir, $baseurl, 'followupsort', 'followupdir'
            ),
            $this->sortable_header(
                get_string('followup_field_priority', 'local_monlaututoria'), 'priority',
                $currentsort, $currentdir, $baseurl, 'followupsort', 'followupdir'
            ),
            get_string('followup_field_status', 'local_monlaututoria'),
            '',
        ];

        foreach ($followups as $followup) {
            $student = $students[$followup->studentid] ?? null;
            $statuslabel = $statusoptions[$followup->status] ?? $followup->status;
            if ($followup->is_overdue()) {
                $statuslabel = get_string('followupstatus_overdue', 'local_monlaututoria') . ' (' . $statuslabel . ')';
            }

            $table->data[] = [
                $student ? s(fullname($student)) : '#' . $followup->studentid,
                userdate($followup->duedate, $dateformat),
                $priorityoptions[$followup->priority] ?? $followup->priority,
                $statuslabel,
                $canmanage ? $this->followup_action_links($followup) : '',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \local_monlaututoria\domain\agreement[] $agreements
     * @param array<int, \stdClass> $students keyed by student id
     * @param array<int, \stdClass> $responsibleusers keyed by user id
     * @param bool $canmanage
     * @param string $currentsort 'studentname', 'duedate' or ''
     * @param string $currentdir 'ASC' or 'DESC'
     * @param \moodle_url $baseurl current page URL with every other filter
     *                             already on it except agreementsort/agreementdir
     * @return string
     */
    public function dashboard_agreements_table(
        array $agreements,
        array $students,
        array $responsibleusers,
        bool $canmanage,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl
    ): string {
        if (empty($agreements)) {
            return $this->subtle_empty_hint(get_string('dashboard_agreements_empty', 'local_monlaututoria'));
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $responsibletypeoptions = \local_monlaututoria\domain\agreement_responsible_type::get_options();
        $statusoptions = \local_monlaututoria\domain\agreement_status::get_options();

        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('assignment_col_student', 'local_monlaututoria'), 'studentname',
                $currentsort, $currentdir, $baseurl, 'agreementsort', 'agreementdir'
            ),
            get_string('agreement_field_description', 'local_monlaututoria'),
            get_string('agreement_field_responsibletype', 'local_monlaututoria'),
            $this->sortable_header(
                get_string('agreement_field_duedate', 'local_monlaututoria'), 'duedate',
                $currentsort, $currentdir, $baseurl, 'agreementsort', 'agreementdir'
            ),
            get_string('agreement_field_status', 'local_monlaututoria'),
            '',
        ];

        foreach ($agreements as $agreement) {
            $student = $students[$agreement->studentid] ?? null;
            $responsiblelabel = $responsibletypeoptions[$agreement->responsibletype] ?? $agreement->responsibletype;
            if ($agreement->responsibleuserid !== null) {
                $responsibleuser = $responsibleusers[$agreement->responsibleuserid] ?? null;
                $responsiblelabel .= ': ' . ($responsibleuser ? s(fullname($responsibleuser)) : '#' . $agreement->responsibleuserid);
            } else if ($agreement->responsibleexternalname !== null) {
                $responsiblelabel .= ': ' . s($agreement->responsibleexternalname);
            }

            $statuslabel = $statusoptions[$agreement->status] ?? $agreement->status;
            if ($agreement->is_overdue()) {
                $statuslabel = get_string('agreementstatus_overdue', 'local_monlaututoria') . ' (' . $statuslabel . ')';
            }

            $table->data[] = [
                $student ? s(fullname($student)) : '#' . $agreement->studentid,
                s($agreement->description),
                $responsiblelabel,
                userdate($agreement->duedate, $dateformat),
                $statuslabel,
                $canmanage ? $this->agreement_action_links($agreement) : '',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \local_monlaututoria\domain\tutor_dashboard_student[] $students
     * @param array<int, \stdClass> $studentusers keyed by student id
     * @param int $academicyearid
     * @return string
     */
    public function dashboard_priority_students_list(array $students, array $studentusers, int $academicyearid): string {
        if (empty($students)) {
            return $this->subtle_empty_hint(get_string('dashboard_priority_empty', 'local_monlaututoria'));
        }

        $items = [];
        foreach ($students as $student) {
            $user = $studentusers[$student->studentid] ?? null;
            $label = $user ? fullname($user) : '#' . $student->studentid;
            $details = 'Seg. ' . $student->openfollowupcount
                . ' / Acu. ' . $student->openagreementcount
                . ' / Der. ' . $student->openreferralcount;
            $items[] = \html_writer::tag(
                'li',
                \html_writer::link(
                    new \moodle_url('/local/monlaututoria/student/view.php', [
                        'id' => $student->studentid,
                        'academicyearid' => $academicyearid,
                    ]),
                    s($label)
                ) . ' - ' . s($details)
            );
        }

        return \html_writer::tag('ul', implode('', $items));
    }

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
    /**
     * @param \stdClass[] $rows
     * @param string $currentsort one of assignment_repository::SORTABLE_COLUMNS,
     *                             or '' — the page has already validated this
     * @param string $currentdir 'ASC' or 'DESC'
     * @param \moodle_url $baseurl current page URL with every filter/page
     *                             param already on it except sort/dir
     * @return string
     */
    public function assignments_list(array $rows, string $currentsort, string $currentdir, \moodle_url $baseurl): string {
        $data = [
            'hasrows' => !empty($rows),
            'rows'    => array_values($rows),
            'message' => get_string('assignments_list_empty', 'local_monlaututoria'),
            'header_timestart' => $this->sortable_header(
                get_string('assignment_col_timestart', 'local_monlaututoria'), 'timestart', $currentsort, $currentdir, $baseurl
            ),
            'header_timeend' => $this->sortable_header(
                get_string('assignment_col_timeend', 'local_monlaututoria'), 'timeend', $currentsort, $currentdir, $baseurl
            ),
            'header_status' => $this->sortable_header(
                get_string('assignment_col_status', 'local_monlaututoria'), 'status', $currentsort, $currentdir, $baseurl
            ),
            'header_type' => $this->sortable_header(
                get_string('assignment_col_type', 'local_monlaututoria'), 'assignmenttype', $currentsort, $currentdir, $baseurl
            ),
            'header_source' => $this->sortable_header(
                get_string('assignment_col_source', 'local_monlaututoria'), 'source', $currentsort, $currentdir, $baseurl
            ),
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
            'resumen'      => get_string('studenttab_summary', 'local_monlaututoria'),
            'historial'    => get_string('studenttab_history', 'local_monlaututoria'),
            'tutorias'     => get_string('studenttab_tutoring', 'local_monlaututoria'),
        ];
        // Fase 13 — "Acuerdos" / "Seguimientos" only when those modules are on.
        if (\local_monlaututoria\feature::enabled(\local_monlaututoria\feature::AGREEMENTS)) {
            $tabs['acuerdos'] = get_string('studenttab_agreements', 'local_monlaututoria');
        }
        if (\local_monlaututoria\feature::enabled(\local_monlaututoria\feature::FOLLOWUPS)) {
            $tabs['seguimientos'] = get_string('studenttab_followups', 'local_monlaututoria');
        }
        $tooltips = [
            'resumen' => get_string('studenttab_summary_tip', 'local_monlaututoria'),
            'historial' => get_string('studenttab_history_tip', 'local_monlaututoria'),
            'tutorias' => get_string('studenttab_tutoring_tip', 'local_monlaututoria'),
            'acuerdos' => get_string('studenttab_agreements_tip', 'local_monlaututoria'),
            'seguimientos' => get_string('studenttab_followups_tip', 'local_monlaututoria'),
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
            $attributes = ['class' => $classes, 'title' => $tooltips[$key] ?? $label];
            if ($key === $active) {
                $attributes['aria-current'] = 'page';
            }
            $links .= \html_writer::link($url, $label, $attributes);
        }

        return \html_writer::div($links, 'nav nav-tabs mb-3 local-monlaututoria-subnav');
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
     * Renders the per-student classification of a cohort assignment preview
     * (the "confirm" step cohort_assignment_preview_service's own docblock
     * names as phases 3C.3-3C.5). Plain html_writer table on an internal
     * admin screen, same rationale as csv_import_preview_table.
     *
     * @param \local_monlaututoria\domain\cohort_assignment_item[] $items
     * @return string
     */
    public function cohort_assignment_preview_table(array $items): string {
        if (empty($items)) {
            return $this->output->notification(
                get_string('cohort_assignment_preview_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        global $DB;

        $userids = [];
        foreach ($items as $item) {
            $userids[$item->studentid] = true;
            if ($item->currentprimarytutorid !== null) {
                $userids[$item->currentprimarytutorid] = true;
            }
        }
        $users = !empty($userids) ? $DB->get_records_list('user', 'id', array_keys($userids)) : [];

        $conflictoptions = \local_monlaututoria\domain\assignment_conflict_code::get_options();

        $table = new \html_table();
        $table->head = [
            get_string('assignment_col_student', 'local_monlaututoria'),
            get_string('cohort_assignment_col_action', 'local_monlaututoria'),
            get_string('cohort_assignment_col_currenttutor', 'local_monlaututoria'),
            get_string('cohort_assignment_col_cotutoraction', 'local_monlaututoria'),
            get_string('cohort_assignment_col_conflicts', 'local_monlaututoria'),
        ];

        foreach ($items as $item) {
            $student = $users[$item->studentid] ?? null;
            $currenttutor = $item->currentprimarytutorid !== null ? ($users[$item->currentprimarytutorid] ?? null) : null;

            $conflictlabels = array_map(
                static fn (string $code): string => $conflictoptions[$code] ?? $code,
                $item->conflictcodes
            );

            $table->data[] = [
                $student ? fullname($student) : ('#' . $item->studentid),
                $this->cohort_assignment_action_badge($item->action),
                $currenttutor ? fullname($currenttutor) : '—',
                $item->cotutoraction !== null ? $this->cohort_assignment_action_badge($item->cotutoraction) : '—',
                !empty($conflictlabels) ? s(implode(', ', $conflictlabels)) : '—',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * Renders the per-student result of an applied cohort assignment
     * operation.
     *
     * @param \local_monlaututoria\domain\cohort_assignment_apply_result_row[] $rows
     * @return string
     */
    public function cohort_assignment_apply_result_table(array $rows): string {
        if (empty($rows)) {
            return $this->output->notification(
                get_string('cohort_assignment_apply_result_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        global $DB;

        $userids = array_unique(array_map(static fn ($row) => $row->studentid, $rows));
        $users = !empty($userids) ? $DB->get_records_list('user', 'id', $userids) : [];

        $table = new \html_table();
        $table->head = [
            get_string('assignment_col_student', 'local_monlaututoria'),
            get_string('cohort_assignment_col_action', 'local_monlaututoria'),
            get_string('cohort_assignment_col_cotutoraction', 'local_monlaututoria'),
        ];

        foreach ($rows as $row) {
            $student = $users[$row->studentid] ?? null;

            $table->data[] = [
                $student ? fullname($student) : ('#' . $row->studentid),
                $this->cohort_assignment_action_badge($row->outcome),
                $row->cotutoroutcome !== null ? $this->cohort_assignment_action_badge($row->cotutoroutcome) : '—',
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param string $action one of cohort_assignment_action::values()
     * @return string
     */
    private function cohort_assignment_action_badge(string $action): string {
        $map = [
            \local_monlaututoria\domain\cohort_assignment_action::CREATE_PRIMARY    => ['success', 'cohort_action_create_primary'],
            \local_monlaututoria\domain\cohort_assignment_action::CREATE_COTUTOR    => ['success', 'cohort_action_create_cotutor'],
            \local_monlaututoria\domain\cohort_assignment_action::REASSIGN_PRIMARY  => ['success', 'cohort_action_reassign_primary'],
            \local_monlaututoria\domain\cohort_assignment_action::CLOSE_MISSING     => ['warning', 'cohort_action_close_missing'],
            \local_monlaututoria\domain\cohort_assignment_action::NO_CHANGE         => ['secondary', 'cohort_action_no_change'],
            \local_monlaututoria\domain\cohort_assignment_action::SKIP_EXISTING     => ['secondary', 'cohort_action_skip_existing'],
            \local_monlaututoria\domain\cohort_assignment_action::SKIP_SUSPENDED    => ['warning', 'cohort_action_skip_suspended'],
            \local_monlaututoria\domain\cohort_assignment_action::SKIP_INVALID      => ['warning', 'cohort_action_skip_invalid'],
            \local_monlaututoria\domain\cohort_assignment_action::CONFLICT_PRIMARY  => ['danger', 'cohort_action_conflict_primary'],
            \local_monlaututoria\domain\cohort_assignment_action::ERROR             => ['danger', 'cohort_action_error'],
        ];
        [$class, $stringkey] = $map[$action] ?? ['secondary', $action];

        return \html_writer::span(get_string($stringkey, 'local_monlaututoria'), 'badge badge-' . $class);
    }

    /**
     * Renders the "Tutorías" history tab of the student ficha (phase 5.4):
     * a chronological table of tutoring entries within the selected academic
     * year. Only metadata columns — content/notes stay on the detail page
     * (entries/view.php), same split as assignments (listing vs. view.php).
     *
     * @param \local_monlaututoria\domain\entry[] $entries already masked by
     *                                            entry_service::get_history_for_student()
     * @param array<int, \stdClass> $tutors keyed by tutorid
     * @param array<int, \stdClass> $modalities keyed by modalityid
     * @param array<int, int[]> $reasonsbyentry keyed by entry id
     * @param array<int, \stdClass> $allreasons keyed by reasonid, used to resolve
     *                                          $reasonsbyentry into names
     * @param bool $islimitedview phase 4.3-style limited view: true when the
     *                            viewer is the student themselves — omits the
     *                            "Motivos" column and the link to the detail
     *                            page (mismo criterio que
     *                            student_history_table() para asignaciones)
     * @return string
     */
    public function entry_history_table(
        array $entries,
        array $tutors,
        array $modalities,
        array $reasonsbyentry,
        array $allreasons,
        bool $islimitedview,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl
    ): string {
        if (empty($entries)) {
            return $this->output->notification(
                get_string('entry_history_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $statusoptions = \local_monlaututoria\domain\entry_status::get_options();

        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('entry_field_entrydate', 'local_monlaututoria'), 'entrydate',
                $currentsort, $currentdir, $baseurl, 'entrysort', 'entrydir'
            ),
            get_string('assignment_col_tutor', 'local_monlaututoria'),
            get_string('entry_field_modality', 'local_monlaututoria'),
            $this->sortable_header(
                get_string('assignment_col_status', 'local_monlaututoria'), 'status',
                $currentsort, $currentdir, $baseurl, 'entrysort', 'entrydir'
            ),
        ];
        if (!$islimitedview) {
            $table->head[] = get_string('entry_field_reasons', 'local_monlaututoria');
            $table->head[] = '';
        }

        foreach ($entries as $entry) {
            $tutor = $tutors[$entry->tutorid] ?? null;
            $modality = $entry->modalityid !== null ? ($modalities[$entry->modalityid] ?? null) : null;

            $cells = [
                userdate($entry->entrydate, $dateformat),
                // Same rationale as student_history_table(): html_writer::table()
                // never auto-escapes, unlike Mustache, so a real user's name
                // needs an explicit s() here.
                $tutor ? s(fullname($tutor)) : '#' . $entry->tutorid,
                $modality ? format_string($modality->name) : '—',
                $statusoptions[$entry->status] ?? $entry->status,
            ];

            if (!$islimitedview) {
                $reasonids = $reasonsbyentry[$entry->id] ?? [];
                $reasonnames = array_filter(array_map(
                    static fn (int $reasonid) => isset($allreasons[$reasonid]) ? format_string($allreasons[$reasonid]->name) : null,
                    $reasonids
                ));
                $cells[] = !empty($reasonnames) ? s(implode(', ', $reasonnames)) : '—';
                $cells[] = \html_writer::link(
                    new \moodle_url('/local/monlaututoria/entries/view.php', ['id' => $entry->id]),
                    get_string('entry_viewdetail', 'local_monlaututoria')
                );
            }

            $table->data[] = $cells;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * Renders the "Acuerdos" tab listing (phase 6.1/6.3). $canmanage gates
     * the complete/reopen/postpone/cancel action links — a student viewing
     * their own visible-to-them agreements never sees them, regardless of
     * any capability, same "whose file is this" reasoning already applied
     * elsewhere in this renderer.
     *
     * @param \local_monlaututoria\domain\agreement[] $agreements
     * @param array $responsibleusers keyed by user id, from core_user::get_user() batches
     * @param bool $canmanage
     * @return string
     */
    public function agreements_table(array $agreements, array $responsibleusers, bool $canmanage): string {
        if (empty($agreements)) {
            return $this->output->notification(
                get_string('agreements_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $statusoptions = \local_monlaututoria\domain\agreement_status::get_options();
        $responsibletypeoptions = \local_monlaututoria\domain\agreement_responsible_type::get_options();

        $table = new \html_table();
        $table->head = [
            get_string('agreement_field_description', 'local_monlaututoria'),
            get_string('agreement_field_responsibletype', 'local_monlaututoria'),
            get_string('agreement_field_duedate', 'local_monlaututoria'),
            get_string('agreement_field_status', 'local_monlaututoria'),
        ];
        if ($canmanage) {
            $table->head[] = '';
        }

        foreach ($agreements as $agreement) {
            $responsiblelabel = $responsibletypeoptions[$agreement->responsibletype] ?? $agreement->responsibletype;
            if ($agreement->responsibleuserid !== null) {
                $responsibleuser = $responsibleusers[$agreement->responsibleuserid] ?? null;
                $responsiblelabel .= ': ' . ($responsibleuser ? s(fullname($responsibleuser)) : '#' . $agreement->responsibleuserid);
            } else if ($agreement->responsibleexternalname !== null) {
                $responsiblelabel .= ': ' . s($agreement->responsibleexternalname);
            }

            $statuslabel = $statusoptions[$agreement->status] ?? $agreement->status;
            if ($agreement->is_overdue()) {
                $statuslabel = get_string('agreementstatus_overdue', 'local_monlaututoria') . ' (' . $statuslabel . ')';
            }

            $cells = [
                s($agreement->description),
                $responsiblelabel,
                userdate($agreement->duedate, $dateformat),
                $statuslabel,
            ];

            if ($canmanage) {
                $cells[] = $this->agreement_action_links($agreement);
            }

            $table->data[] = $cells;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \local_monlaututoria\domain\agreement $agreement
     * @return string
     */
    private function agreement_action_links(\local_monlaututoria\domain\agreement $agreement): string {
        $openvalues = \local_monlaututoria\domain\agreement_status::open_values();
        if (!in_array($agreement->status, $openvalues, true)) {
            return '';
        }

        $links = [
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/agreements/action.php', ['id' => $agreement->id, 'action' => 'complete']),
                get_string('agreement_complete', 'local_monlaututoria')
            ),
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/agreements/postpone.php', ['id' => $agreement->id]),
                get_string('agreement_postpone', 'local_monlaututoria')
            ),
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/agreements/action.php', ['id' => $agreement->id, 'action' => 'cancel']),
                get_string('agreement_cancel', 'local_monlaututoria')
            ),
        ];

        return implode(' | ', $links);
    }

    /**
     * Renders the "Seguimientos" tab listing (phase 6.2/6.3). Staff-only —
     * unlike agreements_table(), never called for a student's own limited
     * view (see followup_service's class docblock for why).
     *
     * @param \local_monlaututoria\domain\followup[] $followups
     * @param bool $canmanage
     * @return string
     */
    public function followups_table(array $followups, bool $canmanage): string {
        if (empty($followups)) {
            return $this->output->notification(
                get_string('followups_empty', 'local_monlaututoria'),
                \core\output\notification::NOTIFY_INFO
            );
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $statusoptions = \local_monlaututoria\domain\followup_status::get_options();
        $priorityoptions = \local_monlaututoria\domain\priority_level::get_options();

        $table = new \html_table();
        $table->head = [
            get_string('followup_field_duedate', 'local_monlaututoria'),
            get_string('followup_field_priority', 'local_monlaututoria'),
            get_string('followup_field_status', 'local_monlaututoria'),
        ];
        if ($canmanage) {
            $table->head[] = '';
        }

        foreach ($followups as $followup) {
            $statuslabel = $statusoptions[$followup->status] ?? $followup->status;
            if ($followup->is_overdue()) {
                $statuslabel = get_string('followupstatus_overdue', 'local_monlaututoria') . ' (' . $statuslabel . ')';
            }

            $cells = [
                userdate($followup->duedate, $dateformat),
                $priorityoptions[$followup->priority] ?? $followup->priority,
                $statuslabel,
            ];

            if ($canmanage) {
                $cells[] = $this->followup_action_links($followup);
            }

            $table->data[] = $cells;
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * @param \local_monlaututoria\domain\followup $followup
     * @return string
     */
    private function followup_action_links(\local_monlaututoria\domain\followup $followup): string {
        if (!in_array($followup->status, \local_monlaututoria\domain\followup_status::open_values(), true)) {
            return '';
        }

        $links = [
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/followups/action.php', ['id' => $followup->id, 'action' => 'complete']),
                get_string('followup_complete', 'local_monlaututoria')
            ),
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/followups/postpone.php', ['id' => $followup->id]),
                get_string('agreement_postpone', 'local_monlaututoria')
            ),
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/followups/action.php', ['id' => $followup->id, 'action' => 'cancel']),
                get_string('followup_cancel', 'local_monlaututoria')
            ),
            // "Cierre... mediante nueva tutoría vinculada" (docs/fases/phase-6.md):
            // reuses the quick registration page rather than a dedicated flow.
            \html_writer::link(
                new \moodle_url('/local/monlaututoria/entries/create.php', [
                    'studentid' => $followup->studentid, 'followupid' => $followup->id,
                ]),
                get_string('entry_field_followup', 'local_monlaututoria')
            ),
        ];

        return implode(' | ', $links);
    }

    /**
     * Renders the referrals listing for coordination/orientation/management
     * (phase 6.4, referrals/index.php). Never called for a student's own
     * view — there is no such view, referrals have no ficha tab.
     *
     * @param \local_monlaututoria\domain\referral[] $referrals
     * @param array $students keyed by user id
     * @param string $currentsort 'studentname', 'destination', 'priority', 'status' or ''
     * @param string $currentdir 'ASC' or 'DESC'
     * @param \moodle_url $baseurl current page URL with every other filter
     *                             already on it except referralsort/referraldir
     * @return string
     */
    public function referrals_table(
        array $referrals,
        array $students,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl
    ): string {
        if (empty($referrals)) {
            return $this->subtle_empty_hint(get_string('referrals_empty', 'local_monlaututoria'));
        }

        $dateformat = get_string('strftimedatefullshort', 'langconfig');
        $destinationoptions = \local_monlaututoria\domain\referral_destination::get_options();
        $statusoptions = \local_monlaututoria\domain\referral_status::get_options();
        $priorityoptions = \local_monlaututoria\domain\priority_level::get_options();

        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('assignment_col_student', 'local_monlaututoria'), 'studentname',
                $currentsort, $currentdir, $baseurl, 'referralsort', 'referraldir'
            ),
            $this->sortable_header(
                get_string('referral_field_destination', 'local_monlaututoria'), 'destination',
                $currentsort, $currentdir, $baseurl, 'referralsort', 'referraldir'
            ),
            $this->sortable_header(
                get_string('followup_field_priority', 'local_monlaututoria'), 'priority',
                $currentsort, $currentdir, $baseurl, 'referralsort', 'referraldir'
            ),
            $this->sortable_header(
                get_string('referral_field_status', 'local_monlaututoria'), 'status',
                $currentsort, $currentdir, $baseurl, 'referralsort', 'referraldir'
            ),
            '',
        ];

        foreach ($referrals as $referral) {
            $student = $students[$referral->studentid] ?? null;

            $table->data[] = [
                $student ? s(fullname($student)) : '#' . $referral->studentid,
                $destinationoptions[$referral->destination] ?? $referral->destination,
                $priorityoptions[$referral->priority] ?? $referral->priority,
                $statusoptions[$referral->status] ?? $referral->status,
                \html_writer::link(
                    new \moodle_url('/local/monlaututoria/referrals/view.php', ['id' => $referral->id]),
                    get_string('referral_viewdetail', 'local_monlaututoria')
                ),
            ];
        }

        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    /**
     * Renders the detail view of a single referral (phase 6.4). Plain
     * html_writer, not a Mustache template like entry_detail() — a
     * definition list with no repeating/conditional structure complex
     * enough to warrant one.
     *
     * @param \local_monlaututoria\domain\referral $referral
     * @param \stdClass $student
     * @param \stdClass|null $entry raw local_tut_entry row it originated from
     * @param \stdClass|null $assignee
     * @return string
     */
    public function referral_detail(\local_monlaututoria\domain\referral $referral, \stdClass $student, ?\stdClass $entry, ?\stdClass $assignee): string {
        $destinationoptions = \local_monlaututoria\domain\referral_destination::get_options();
        $statusoptions = \local_monlaututoria\domain\referral_status::get_options();
        $priorityoptions = \local_monlaututoria\domain\priority_level::get_options();

        $rows = [
            [get_string('assignment_col_student', 'local_monlaututoria'), \html_writer::link(
                new \moodle_url('/local/monlaututoria/student/view.php', ['id' => $student->id]),
                s(fullname($student))
            )],
            [get_string('referral_field_destination', 'local_monlaututoria'), $destinationoptions[$referral->destination] ?? $referral->destination],
            [get_string('referral_field_reason', 'local_monlaututoria'), \html_writer::tag('div', s($referral->reason))],
            [get_string('followup_field_priority', 'local_monlaututoria'), $priorityoptions[$referral->priority] ?? $referral->priority],
            [get_string('referral_field_status', 'local_monlaututoria'), $statusoptions[$referral->status] ?? $referral->status],
            [get_string('referral_field_assignedto', 'local_monlaututoria'), $assignee ? s(fullname($assignee)) : '—'],
            [get_string('referral_field_resolution', 'local_monlaututoria'), $referral->resolution !== null ? \html_writer::tag('div', s($referral->resolution)) : '—'],
        ];
        if ($entry !== null) {
            $rows[] = [get_string('referral_field_originentry', 'local_monlaututoria'), \html_writer::link(
                new \moodle_url('/local/monlaututoria/entries/view.php', ['id' => $entry->id]),
                get_string('entry_viewdetail', 'local_monlaututoria')
            )];
        }

        $html = \html_writer::start_tag('dl', ['class' => 'row']);
        foreach ($rows as [$label, $value]) {
            $html .= \html_writer::tag('dt', $label, ['class' => 'col-sm-3']);
            $html .= \html_writer::tag('dd', $value, ['class' => 'col-sm-9']);
        }
        $html .= \html_writer::end_tag('dl');

        return $html;
    }

    /**
     * Renders the detail view of a single tutoring entry (phase 5.4).
     *
     * @param \stdClass $data already merged with display data (see entries/view.php)
     * @return string
     */
    public function entry_detail(\stdClass $data): string {
        return $this->render_from_template('local_monlaututoria/entry_detail', (array) $data);
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

    public function coordination_summary_cards(\local_monlaututoria\domain\coordination_dashboard_summary $summary): string {
        $cards = [
            ['label' => get_string('coordination_summary_population', 'local_monlaututoria'), 'value' => $summary->populationcount],
            ['label' => get_string('coordination_summary_withinitial', 'local_monlaututoria'), 'value' => $summary->withinitialcount],
            ['label' => get_string('coordination_summary_withoutentry', 'local_monlaututoria'), 'value' => $summary->withoutentrycount],
            ['label' => get_string('coordination_summary_overduefollowups', 'local_monlaututoria'), 'value' => $summary->overduefollowupcount],
            ['label' => get_string('coordination_summary_opencases', 'local_monlaututoria'), 'value' => $summary->opencasecount],
        ];
        $html = '';
        foreach ($cards as $card) {
            $html .= \html_writer::div(\html_writer::div(s((string) $card['value']), 'h3 mb-1') . \html_writer::div(s($card['label']), 'text-muted'), 'local-monlaututoria-stat-card');
        }
        return \html_writer::div($html, 'local-monlaututoria-dashboard-summary d-grid gap-3 mb-4');
    }

    public function coordination_quality_cards(\local_monlaututoria\domain\coordination_quality_summary $quality): string {
        $cards = [
            ['label' => get_string('coordination_quality_timetofirst', 'local_monlaututoria'), 'value' => format_float($quality->averagedaystofirstentry, 2) . ' d'],
            ['label' => get_string('coordination_quality_agreements', 'local_monlaututoria'), 'value' => format_float($quality->agreementcompletionpercent, 2) . ' %'],
            ['label' => get_string('coordination_quality_followups', 'local_monlaututoria'), 'value' => format_float($quality->followupontimepercent, 2) . ' %'],
            ['label' => get_string('coordination_quality_familycontacts', 'local_monlaututoria'), 'value' => $quality->familycontactcount],
            ['label' => get_string('coordination_quality_continuity', 'local_monlaututoria'), 'value' => format_float($quality->continuitypercent, 2) . ' %'],
        ];
        $html = '';
        foreach ($cards as $card) {
            $html .= \html_writer::div(\html_writer::div(s((string) $card['value']), 'h3 mb-1') . \html_writer::div(s($card['label']), 'text-muted'), 'local-monlaututoria-stat-card');
        }
        return \html_writer::div($html, 'local-monlaututoria-dashboard-summary d-grid gap-3 mb-4');
    }

    public function coordination_breakdown_table(
        array $rows,
        string $currentsort,
        string $currentdir,
        \moodle_url $baseurl,
        string $sortparam,
        string $dirparam
    ): string {
        if (empty($rows)) {
            return $this->output->notification(get_string('coordination_dashboard_empty', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);
        }
        $table = new \html_table();
        $table->head = [
            $this->sortable_header(
                get_string('coordination_breakdown_label', 'local_monlaututoria'), 'label',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
            $this->sortable_header(
                get_string('coordination_breakdown_population', 'local_monlaututoria'), 'studentcount',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
            $this->sortable_header(
                get_string('coordination_breakdown_withinitial', 'local_monlaututoria'), 'withinitialcount',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
            $this->sortable_header(
                get_string('coordination_breakdown_withoutentry', 'local_monlaututoria'), 'withoutentrycount',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
            $this->sortable_header(
                get_string('coordination_breakdown_overduefollowups', 'local_monlaututoria'), 'overduefollowupcount',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
            $this->sortable_header(
                get_string('coordination_breakdown_opencases', 'local_monlaututoria'), 'opencasecount',
                $currentsort, $currentdir, $baseurl, $sortparam, $dirparam
            ),
        ];
        foreach ($rows as $row) {
            $table->data[] = [format_string($row->label), $row->studentcount, $row->withinitialcount, $row->withoutentrycount, $row->overduefollowupcount, $row->opencasecount];
        }
        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

    public function coordination_scope_assignments_table(array $users, array $scopecohortids, array $cohorts): string {
        if (empty($scopecohortids)) {
            return $this->output->notification(get_string('coordination_scope_empty', 'local_monlaututoria'), \core\output\notification::NOTIFY_INFO);
        }
        $table = new \html_table();
        $table->head = [get_string('coordination_scope_user', 'local_monlaututoria'), get_string('coordination_scope_availablecohorts', 'local_monlaututoria')];
        foreach ($scopecohortids as $userid => $cohortids) {
            $labels = [];
            foreach ($cohortids as $cohortid) {
                $labels[] = isset($cohorts[$cohortid]) ? format_string($cohorts[$cohortid]->name) : ('#' . $cohortid);
            }
            $user = $users[$userid] ?? null;
            $table->data[] = [$user ? fullname($user) : ('#' . $userid), s(implode(', ', $labels))];
        }
        return \html_writer::div(\html_writer::table($table), 'table-responsive');
    }

}



