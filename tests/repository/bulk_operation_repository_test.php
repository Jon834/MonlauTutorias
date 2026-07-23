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

namespace local_monlaututoria\repository;

use local_monlaututoria\domain\bulk_operation_status;

/**
 * Tests for bulk_operation_repository.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class bulk_operation_repository_test extends \advanced_testcase {

    public function test_create_and_get_by_uuid(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $repository = new bulk_operation_repository();

        $id = $repository->create((object) [
            'operationuuid'  => 'test-uuid-1234',
            'cohortid'       => 1,
            'academicyearid' => 1,
            'primarytutorid' => $tutor->id,
            'mode'           => 'add_only',
            'summaryjson'    => json_encode(['totalmembers' => 5]),
            'createdby'      => get_admin()->id,
        ]);

        $record = $repository->get_by_uuid('test-uuid-1234');

        $this->assertSame($id, (int) $record->id);
        $this->assertSame(bulk_operation_status::DRAFT, $record->status);
        $this->assertSame('cohort_assignment', $record->operationtype);
        $this->assertNull($record->cotutorid);
    }

    public function test_get_by_uuid_missing_throws(): void {
        $this->resetAfterTest();

        $repository = new bulk_operation_repository();

        $this->expectException(\dml_missing_record_exception::class);
        $repository->get_by_uuid('does-not-exist');
    }

    public function test_update_status_and_summary(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $repository = new bulk_operation_repository();

        $id = $repository->create((object) [
            'operationuuid'  => 'test-uuid-5678',
            'cohortid'       => 1,
            'academicyearid' => 1,
            'primarytutorid' => $tutor->id,
            'mode'           => 'add_only',
            'createdby'      => get_admin()->id,
        ]);

        $repository->update_status($id, bulk_operation_status::PREVIEWED);
        $repository->update_summary($id, json_encode(['totalmembers' => 10]));

        $record = $repository->get($id);
        $this->assertSame(bulk_operation_status::PREVIEWED, $record->status);
        $this->assertSame(['totalmembers' => 10], json_decode($record->summaryjson, true));
    }

    public function test_create_without_cohort_operation_fields_stores_null(): void {
        $this->resetAfterTest();

        $repository = new bulk_operation_repository();
        $id = $repository->create((object) [
            'operationuuid' => 'test-uuid-csv',
            'operationtype' => 'csv_import',
            'createdby'     => get_admin()->id,
        ]);

        $record = $repository->get($id);
        $this->assertNull($record->cohortid);
        $this->assertNull($record->academicyearid);
        $this->assertNull($record->primarytutorid);
        $this->assertNull($record->mode);
        $this->assertSame('csv_import', $record->operationtype);
    }

    public function test_is_expired(): void {
        $this->resetAfterTest();

        $repository = new bulk_operation_repository();
        $id = $repository->create((object) [
            'operationuuid' => 'test-uuid-expiry',
            'createdby'     => get_admin()->id,
        ]);
        $record = $repository->get($id);

        $this->assertFalse($repository->is_expired($record->operationuuid, 1800));
        $this->assertTrue($repository->is_expired($record->operationuuid, -1));
    }

    public function test_generate_uuid_produces_distinct_valid_uuids(): void {
        $uuid1 = bulk_operation_repository::generate_uuid();
        $uuid2 = bulk_operation_repository::generate_uuid();

        $this->assertNotSame($uuid1, $uuid2);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid1
        );
    }
}
