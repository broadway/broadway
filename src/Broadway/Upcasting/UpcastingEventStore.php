<?php

declare(strict_types=1);

namespace Broadway\Upcasting;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

/**
 * @template ES of mixed
 *
 * @template-implements EventStore<ES>
 */
final readonly class UpcastingEventStore implements EventStore, EventStoreManagement
{
    /**
     * @param EventStoreManagement&EventStore<mixed> $eventStore
     */
    public function __construct(
        private EventStore&EventStoreManagement $eventStore,
        private UpcasterChain $upcasterChain,
    ) {
    }

    /**
     * @return DomainEventStream<DomainMessage>
     */
    public function load(mixed $id): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->load($id),
            $id,
        );
    }

    /**
     * @param DomainEventStream<DomainMessage> $eventStream
     *
     * @return DomainEventStream<DomainMessage>
     */
    private function upcastStream(DomainEventStream $eventStream, mixed $id): DomainEventStream
    {
        $upcastedEvents = [];

        foreach ($eventStream as $domainMessage) {
            $upcastedEvents[] = $this->upcasterChain->upcast($domainMessage);
        }

        return new DomainEventStream($upcastedEvents);
    }

    /**
     * @return DomainEventStream<DomainMessage>
     */
    public function loadFromPlayhead(mixed $id, int $playhead): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->loadFromPlayhead($id, $playhead),
            $id
        );
    }

    /**
     * @param DomainEventStream<DomainMessage> $eventStream
     */
    public function append(mixed $id, DomainEventStream $eventStream): void
    {
        $this->eventStore->append($id, $eventStream);
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        $this->eventStore->visitEvents($criteria, $eventVisitor);
    }
}
