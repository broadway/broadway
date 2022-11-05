<?php

declare(strict_types=1);

namespace Broadway\Upcasting;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

final class UpcastingEventStore implements EventStore, EventStoreManagement
{
    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var UpcasterChain
     */
    private $upcasterChain;

    public function __construct(EventStore $eventStore, UpcasterChain $upcasterChain)
    {
        $this->eventStore = $eventStore;
        $this->upcasterChain = $upcasterChain;
    }

    public function load($id): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->load($id),
            $id
        );
    }

    private function upcastStream(DomainEventStream $eventStream, $id): DomainEventStream
    {
        $upcastedEvents = [];

        foreach ($eventStream as $domainMessage) {
            $upcastedEvents[] = $this->createUpcastedDomainMessage($domainMessage, $id);
        }

        return new DomainEventStream($upcastedEvents);
    }

    private function createUpcastedDomainMessage(DomainMessage $domainMessage, $id): DomainMessage
    {
        return new DomainMessage(
            $id,
            $domainMessage->getPlayhead(),
            $domainMessage->getMetadata(),
            $this->upcasterChain->upcast($domainMessage->getPayload()),
            $domainMessage->getRecordedOn()
        );
    }

    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->loadFromPlayhead($id, $playhead),
            $id
        );
    }

    public function append($id, DomainEventStream $eventStream): void
    {
        $this->eventStore->append($id, $eventStream);
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        $this->eventStore->visitEvents($criteria, $eventVisitor);
    }
}
