<?php

declare(strict_types=1);

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\Exception\DuplicatePlayheadException;

final class ConcurrencyConflictResolvingEventStore implements EventStore
{
    /** @var EventStore */
    private $eventStore;

    /** @var ConcurrencyConflictResolver */
    private $conflictResolver;

    /**
     * ConcurrencyConflictResolvingEventStore constructor.
     *
     * @param EventStore                  $eventStore
     * @param ConcurrencyConflictResolver $conflictResolver
     */
    public function __construct(EventStore $eventStore, ConcurrencyConflictResolver $conflictResolver)
    {
        $this->eventStore = $eventStore;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStream $uncommittedEvents)
    {
        try {
            $this->eventStore->append($id, $uncommittedEvents);
        } catch (DuplicatePlayheadException $e) {
            $committedEvents = $this->eventStore->load($id);
            $conflictingEvents = $this->getConflictingEvents($uncommittedEvents, $committedEvents);

            $conflictResolvedEvents = [];
            $playhead = $this->getCurrentPlayhead($committedEvents);

            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                foreach ($conflictingEvents as $conflictingEvent) {
                    if ($this->conflictResolver->conflictsWith($conflictingEvent, $uncommittedEvent)) {
                        throw $e;
                    }
                }

                ++$playhead;

                $conflictResolvedEvents[] = new DomainMessage(
                    $id,
                    $playhead,
                    $uncommittedEvent->getMetadata(),
                    $uncommittedEvent->getPayload(),
                    $uncommittedEvent->getRecordedOn());
            }

            $this->append($id, new DomainEventStream($conflictResolvedEvents));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): DomainEventStream
    {
        return $this->eventStore->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    /**
     * @return int
     */
    private function getCurrentPlayhead(DomainEventStream $committedEvents): int
    {
        $events = iterator_to_array($committedEvents);
        /** @var DomainMessage $lastEvent */
        $lastEvent = end($events);
        $playhead = $lastEvent->getPlayhead();

        return $playhead;
    }

    /**
     * @return DomainMessage[]
     */
    private function getConflictingEvents(
        DomainEventStream $uncommittedEvents,
        DomainEventStream $committedEvents
    ): array {
        $conflictingEvents = [];

        /** @var DomainMessage $committedEvent */
        foreach ($committedEvents as $committedEvent) {
            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                if ($committedEvent->getPlayhead() >= $uncommittedEvent->getPlayhead()) {
                    $conflictingEvents[] = $committedEvent;

                    break;
                }
            }
        }

        return $conflictingEvents;
    }
}
