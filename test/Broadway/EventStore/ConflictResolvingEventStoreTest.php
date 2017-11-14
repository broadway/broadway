<?php

declare(strict_types=1);

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\Testing\EventStoreTest;
use Prophecy\Argument;

class ConflictResolvingEventStoreTest extends EventStoreTest
{
    /** @var ConcurrencyConflictResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $concurrencyResolver;

    protected function setUp()
    {
        $this->concurrencyResolver = $this->prophesize(ConcurrencyConflictResolver::class);
        $this->concurrencyResolver
            ->conflictsWith(Argument::type(DomainMessage::class), Argument::type(DomainMessage::class))
            ->willReturn(true);

        $this->eventStore = new ConcurrencyConflictResolvingEventStore(
            new InMemoryEventStore(), $this->concurrencyResolver->reveal());
    }

    /** @test */
    public function events_can_be_appended_although_playheads_conflict_if_events_are_independent()
    {
        $this->concurrencyResolver
            ->conflictsWith(Argument::type(DomainMessage::class), Argument::type(DomainMessage::class))
            ->willReturn(false);

        $domainMessage = $this->createDomainMessage(1, 0);
        $baseStream = new DomainEventStream([$domainMessage]);
        $this->eventStore->append(1, $baseStream);
        $appendedEventStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->append(1, $appendedEventStream);

        $events = $this->eventStore->load(1);
        $this->assertCount(2, $events);
    }
}
