<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ConflictResolvingEventStoreTest extends EventStoreTest
{
    protected ConcurrencyConflictResolver $concurrencyResolver;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        /** @phpstan-var MockObject<ConcurrencyConflictResolver>  $concurrencyResolver */
        $concurrencyResolver = $this
            ->createMock(ConcurrencyConflictResolver::class)
        ;

        $concurrencyResolver
            ->method('conflictsWith')
            ->with(
                $this->isInstanceOf(DomainMessage::class),
                $this->isInstanceOf(DomainMessage::class)
            )
            ->willReturn(true);

        $this->concurrencyResolver = $concurrencyResolver;

        $this->eventStore = new ConcurrencyConflictResolvingEventStore(
            new InMemoryEventStore(), $this->concurrencyResolver);
    }

    #[Test]
    public function events_can_be_appended_although_playheads_conflict_if_events_are_independent(): void
    {
        $concurrencyResolver = $this->createMock(ConcurrencyConflictResolver::class);
        $concurrencyResolver
            ->method('conflictsWith')
            ->willReturn(false);

        $this->eventStore = new ConcurrencyConflictResolvingEventStore(
            new InMemoryEventStore(), $concurrencyResolver);

        $domainMessage = $this->createDomainMessage(1, 0);
        $baseStream = new DomainEventStream([$domainMessage]);
        $this->eventStore->append(1, $baseStream);
        $appendedEventStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->append(1, $appendedEventStream);

        $events = $this->eventStore->load(1);
        $this->assertCount(2, $events);
    }
}
