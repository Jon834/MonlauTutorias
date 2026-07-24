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

use local_monlaututoria\domain\csv_import_preview_row;
use local_monlaututoria\domain\csv_import_row_status;
use local_monlaututoria\domain\csv_import_apply_result_row;
use local_monlaututoria\domain\csv_import_row_outcome;
use local_monlaututoria\domain\student_summary;

/**
 * XSS regression tests for renderer (Fase 3E.2 — "pruebas ... XSS" del
 * trabajo obligatorio de la Fase 3E). Every value rendered here can
 * originate from an administrator-supplied CSV file or a listing built from
 * ordinary Moodle user records — never trusted, always expected to come out
 * escaped regardless of what went in.
 *
 * The assignments_list()/assignment_detail() cases deliberately feed a
 * hostile value directly into the renderer context, bypassing whatever
 * escaping the calling page (assignments/index.php, assignments/view.php)
 * might already apply (format_string(), fullname(), format_text()) — this
 * verifies the Mustache template itself escapes on render (double braces,
 * never triple), as a second, independent line of defence, not merely that
 * the page happens to sanitise its inputs first.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class renderer_test extends \advanced_testcase {

    /** @var string */
    private const PAYLOAD = '<script>alert(document.cookie)</script>';

    /**
     * @return renderer
     */
    private function get_renderer(): renderer {
        global $PAGE;

        return $PAGE->get_renderer('local_monlaututoria');
    }

    public function test_csv_import_preview_table_escapes_hostile_values(): void {
        $this->resetAfterTest();

        $row = new csv_import_preview_row(
            1,
            ['student' => self::PAYLOAD, 'tutor' => self::PAYLOAD, 'academicyear' => self::PAYLOAD, 'cohort' => self::PAYLOAD],
            csv_import_row_status::ERROR,
            [],
            null,
            null,
            null,
            null,
            'primary',
            true
        );

        $html = $this->get_renderer()->csv_import_preview_table([$row]);

        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString(s(self::PAYLOAD), $html);
    }

    public function test_csv_import_apply_result_table_escapes_hostile_values(): void {
        $this->resetAfterTest();

        $row = new csv_import_apply_result_row(
            1,
            csv_import_row_outcome::SKIPPED_ERROR,
            null,
            null,
            ['student' => self::PAYLOAD, 'tutor' => self::PAYLOAD]
        );

        $html = $this->get_renderer()->csv_import_apply_result_table([$row]);

        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString(s(self::PAYLOAD), $html);
    }

    public function test_assignments_list_escapes_hostile_row_values(): void {
        $this->resetAfterTest();

        $rows = [[
            'status' => 'active', 'statuslabel' => 'Active', 'statusclass' => 'success', 'statusicon' => 'check-circle',
            'studentname' => self::PAYLOAD, 'tutorname' => self::PAYLOAD, 'cotutornames' => self::PAYLOAD,
            'cohortname' => '—', 'academicyearname' => '2026-2027', 'typelabel' => 'Primary',
            'timestartformatted' => '1 Sep 2026', 'timeendformatted' => '—', 'sourcelabel' => 'Manual',
            'detailurl' => 'https://example.org/view.php?id=1', 'viewdetaillabel' => 'View',
            'canedit' => false, 'editurl' => '', 'editlabel' => '',
            'canclose' => false, 'closeurl' => '', 'closelabel' => '',
        ]];

        $html = $this->get_renderer()->assignments_list($rows);

        // Substring check, not an exact match against s(): Moodle's Mustache
        // escaper is not guaranteed byte-for-byte identical to s(), only
        // guaranteed to neutralise the dangerous characters.
        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_assignment_detail_escapes_hostile_note_and_names(): void {
        $this->resetAfterTest();

        $data = (object) [
            'status' => 'active', 'statuslabel' => 'Active', 'statusclass' => 'success', 'statusicon' => 'check-circle',
            'studentname' => self::PAYLOAD, 'tutorname' => 'John Tutor', 'typelabel' => 'Primary',
            'academicyearname' => '2026-2027', 'cohortname' => '—',
            'timestartformatted' => '1 Sep 2026', 'timeendformatted' => '—', 'sourcelabel' => 'Manual',
            'noteformatted' => self::PAYLOAD, 'closereasonlabel' => '—',
            'createdbyname' => 'Admin', 'createdonformatted' => '1 Sep 2026, 09:00',
            'modifiedbyname' => 'Admin', 'modifiedonformatted' => '1 Sep 2026, 09:00',
            'canedit' => false, 'editurl' => '', 'editlabel' => '',
            'canclose' => false, 'closeurl' => '', 'closelabel' => '',
        ];

        $html = $this->get_renderer()->assignment_detail($data);

        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_student_history_table_escapes_hostile_tutor_name(): void {
        $this->resetAfterTest();

        $hostiletutor = $this->getDataGenerator()->create_user(['firstname' => self::PAYLOAD, 'lastname' => 'Tutor']);

        $row = (object) [
            'id' => 1, 'tutorid' => $hostiletutor->id, 'academicyearid' => 1,
            'assignmenttype' => 'primary', 'status' => 'active', 'timestart' => time(), 'timeend' => null,
            'source' => 'manual', 'closereason' => null, 'reassignreason' => null,
        ];

        $html = $this->get_renderer()->student_history_table([$row], [$hostiletutor->id => $hostiletutor], []);

        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString(s(self::PAYLOAD), $html);
    }

    public function test_student_summary_escapes_hostile_tutor_name(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $hostiletutor = $this->getDataGenerator()->create_user(['firstname' => self::PAYLOAD, 'lastname' => 'Tutor']);
        $academicyear = (object) ['id' => 1, 'name' => '2026-2027'];

        $primaryrow = (object) ['id' => 1, 'tutorid' => $hostiletutor->id, 'cohortid' => null];
        $summary = new student_summary($student->id, 1, $primaryrow, [], null, []);

        $html = $this->get_renderer()->student_summary($summary, $academicyear, $student);

        $this->assertStringNotContainsString(self::PAYLOAD, $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_student_summary_limited_view_has_no_links_to_assignment_detail(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyear = (object) ['id' => 1, 'name' => '2026-2027'];

        $primaryrow = (object) ['id' => 1, 'tutorid' => $tutor->id, 'cohortid' => null];
        $summary = new student_summary($student->id, 1, $primaryrow, [], null, []);

        $html = $this->get_renderer()->student_summary($summary, $academicyear, $student, true);

        // The tutor's name is still shown, just never as a link to a page
        // (assignments/view.php) the student has no capability to open.
        $this->assertStringContainsString(fullname($tutor), $html);
        $this->assertStringNotContainsString('/local/monlaututoria/assignments/view.php', $html);
    }

    public function test_student_summary_full_view_links_to_assignment_detail(): void {
        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $tutor = $this->getDataGenerator()->create_user();
        $academicyear = (object) ['id' => 1, 'name' => '2026-2027'];

        $primaryrow = (object) ['id' => 1, 'tutorid' => $tutor->id, 'cohortid' => null];
        $summary = new student_summary($student->id, 1, $primaryrow, [], null, []);

        $html = $this->get_renderer()->student_summary($summary, $academicyear, $student, false);

        $this->assertStringContainsString('/local/monlaututoria/assignments/view.php', $html);
    }

    public function test_student_history_table_limited_view_omits_reason_and_source_and_link(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $row = (object) [
            'id' => 1, 'tutorid' => $tutor->id, 'academicyearid' => 1,
            'assignmenttype' => 'primary', 'status' => 'closed', 'timestart' => time(), 'timeend' => time(),
            'source' => 'manual', 'closereason' => 'tutor_change', 'reassignreason' => null,
        ];

        $html = $this->get_renderer()->student_history_table([$row], [$tutor->id => $tutor], [], true);

        $this->assertStringNotContainsString('/local/monlaututoria/assignments/view.php', $html);
        $this->assertStringNotContainsString(get_string('closereason_tutorchange', 'local_monlaututoria'), $html);
        $this->assertStringNotContainsString(get_string('assignment_col_source', 'local_monlaututoria'), $html);
    }

    /**
     * Phase 4.4 ("Sin N+1"): core_user::get_user() is not cached for
     * ordinary ids (confirmed by reading Moodle core's user.php — every call
     * falls through to a fresh $DB->get_record()), so calling it once per
     * cotutor used to cost one extra query per cotutor. student_summary()
     * now batches every tutor id it needs (primary, cotutors, last, upcoming)
     * into a single get_records_list() call — reads must stay flat as the
     * cotutor count grows, not scale with it.
     */
    public function test_student_summary_batches_tutor_lookups_in_one_query(): void {
        $this->resetAfterTest();

        global $DB;

        $student = $this->getDataGenerator()->create_user();
        $academicyear = (object) ['id' => 1, 'name' => '2026-2027'];
        $primarytutor = $this->getDataGenerator()->create_user();
        $primaryrow = (object) ['id' => 1, 'tutorid' => $primarytutor->id, 'cohortid' => null];

        $onecotutor = $this->getDataGenerator()->create_user();
        $summaryone = new student_summary(
            $student->id, 1, $primaryrow, [(object) ['id' => 2, 'tutorid' => $onecotutor->id]], null, []
        );

        $readsbefore = $DB->perf_get_reads();
        $this->get_renderer()->student_summary($summaryone, $academicyear, $student);
        $readsone = $DB->perf_get_reads() - $readsbefore;

        // 5 distinct cotutors instead of 1 — a per-row core_user::get_user()
        // loop would cost noticeably more reads here; a single batched query
        // costs the same regardless of how many distinct tutors are involved.
        $cotutorrows = [];
        for ($i = 0; $i < 5; $i++) {
            $cotutor = $this->getDataGenerator()->create_user();
            $cotutorrows[] = (object) ['id' => 100 + $i, 'tutorid' => $cotutor->id];
        }
        $summaryfive = new student_summary($student->id, 1, $primaryrow, $cotutorrows, null, []);

        $readsbefore2 = $DB->perf_get_reads();
        $this->get_renderer()->student_summary($summaryfive, $academicyear, $student);
        $readsfive = $DB->perf_get_reads() - $readsbefore2;

        $this->assertSame($readsone, $readsfive);
    }

    /**
     * Phase 4.4 ("Navegación por teclado"): the tabs are real page links, not
     * a JS-toggled ARIA tablist, so keyboard access already works natively
     * (Tab/Enter on <a href>). aria-current="page" is the accessibility
     * signal that should be added on top of that, marking which tab is
     * currently open for screen reader users — same pattern as a breadcrumb.
     */
    public function test_student_tabs_marks_only_the_active_tab_with_aria_current(): void {
        $this->resetAfterTest();

        $html = $this->get_renderer()->student_tabs('historial', 5, null);

        $this->assertSame(1, substr_count($html, 'aria-current="page"'));

        $historiallabel = get_string('studenttab_history', 'local_monlaututoria');
        preg_match('/<a[^>]*>' . preg_quote($historiallabel, '/') . '<\/a>/', $html, $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringContainsString('aria-current="page"', $matches[0]);
    }
}
