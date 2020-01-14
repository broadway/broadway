<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\Testing\EventStoreTest;
use Prophecy\Argument;
use PHPUnit\Framework\MockObject\MockObject;

class ConflictResolvingEventStoreTest extends EventStoreTest
{
    /** @var ConcurrencyConflictResolver|MockObject */
    protected $concurrencyResolver;

    protected function setUp(): void
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
