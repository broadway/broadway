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
use Broadway\EventStore\Exception\DuplicatePlayheadException;

final class ConcurrencyConflictResolvingEventStore implements EventStore
{
    /** @var EventStore */
    private $eventStore;

    /** @var ConcurrencyConflictResolver */
    private $conflictResolver;

    public function __construct(EventStore $eventStore, ConcurrencyConflictResolver $conflictResolver)
    {
        $this->eventStore = $eventStore;
        $this->conflictResolver = $conflictResolver;
    }

    public function append($id, DomainEventStream $uncommittedEvents): void
    {
        $id = (string) $id;

        if (empty(iterator_to_array($uncommittedEvents))) {
            return;
        }

        try {
            $this->eventStore->append($id, $uncommittedEvents);
        } catch (DuplicatePlayheadException $e) {
            $uncommittedPlayhead = $this->getStartingPlayhead($uncommittedEvents);

            $committedEvents = $this->eventStore->loadFromPlayhead($id, $uncommittedPlayhead);
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

    public function load($id): DomainEventStream
    {
        return $this->eventStore->load($id);
    }

    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    private function getCurrentPlayhead(DomainEventStream $committedEvents): int
    {
        $events = iterator_to_array($committedEvents);
        /** @var DomainMessage $lastEvent */
        $lastEvent = end($events);
        $playhead = $lastEvent->getPlayhead();

        return $playhead;
    }

    private function getStartingPlayhead(DomainEventStream $uncommittedEvents): int
    {
        $events = iterator_to_array($uncommittedEvents);
        /** @var DomainMessage $firstEvent */
        $firstEvent = current($events);
        $playhead = $firstEvent->getPlayhead();

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
