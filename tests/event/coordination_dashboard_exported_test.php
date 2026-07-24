<?php
namespace local_monlaututoria\event;

final class coordination_dashboard_exported_test extends \advanced_testcase {

    public function test_event_contains_aggregate_export_metadata_only(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $cohort = $this->getDataGenerator()->create_cohort();

        $sink = $this->redirectEvents();
        coordination_dashboard_exported::create_from_export($user->id, 123, [$cohort->id], 'csv', 7, null)->trigger();
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(coordination_dashboard_exported::class, $events[0]);
        $this->assertSame('cohort', $events[0]->objecttable);
        $this->assertSame(123, $events[0]->other['academicyearid']);
        $this->assertSame('csv', $events[0]->other['format']);
        $this->assertSame(7, $events[0]->other['rowcount']);
        $this->assertArrayNotHasKey('notes', $events[0]->other);
    }
}
