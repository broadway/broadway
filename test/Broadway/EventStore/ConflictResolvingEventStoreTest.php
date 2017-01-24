<?php
namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;

class ConflictResolvingEventStoreTest extends EventStoreTest
{
    /** @var ConcurrencyConflictResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $concurrencyResolver;

    public function setUp()
    {
        $this->concurrencyResolver = $this->getMock(ConcurrencyConflictResolver::class);
        $this->concurrencyResolver->method('conflictsWith')
                                  ->willReturn(true);

        $this->eventStore          = new ConcurrencyConflictResolvingEventStore(
            new InMemoryEventStore(), $this->concurrencyResolver);
    }

    /** @test */
    public function events_can_be_appended_although_playheads_conflict_if_events_are_independent()
    {
        $this->concurrencyResolver = $this->getMock(ConcurrencyConflictResolver::class);
        $this->concurrencyResolver->method('conflictsWith')
                                  ->willReturn(false);

        $this->eventStore = new ConcurrencyConflictResolvingEventStore(
            new InMemoryEventStore(), $this->concurrencyResolver);

        $domainMessage = $this->createDomainMessage(1, 0);
        $baseStream    = new DomainEventStream([$domainMessage]);
        $this->eventStore->append(1, $baseStream);
        $appendedEventStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->append(1, $appendedEventStream);

        $events = $this->eventStore->load(1);
        $this->assertCount(2, $events);
    }
}
