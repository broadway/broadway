<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagementInterface;

/**
 * In-memory implementation of an event store.
 *
 * Useful for testing code that uses an event store.
 */
class InMemoryEventStore implements EventStoreInterface, EventStoreManagementInterface
{
    private $events = array();

    /**
     * {@inheritDoc}
     */
    public function load($streamType, $identifier)
    {
        $identifier = (string) $identifier;

        if (isset($this->events[$streamType][$identifier])) {
            return new DomainEventStream($this->events[$streamType][$identifier]);
        }

        throw new EventStreamNotFoundException(
            sprintf('EventStream not found for aggregate with id %s and type %s', $identifier, $streamType)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function append($streamType, $identifier, DomainEventStreamInterface $eventStream)
    {
        $identifier = (string) $identifier;

        if (! isset($this->events[$streamType][$identifier])) {
            $this->events[$streamType][$identifier] = array();
        }

        foreach ($eventStream as $event) {
            $playhead = $event->getPlayhead();
            $this->assertPlayhead($this->events[$streamType][$identifier], $playhead);

            $this->events[$streamType][$identifier][$playhead] = $event;
        }
    }

    private function assertPlayhead($events, $playhead)
    {
        if (isset($events[$playhead])) {
            throw new InMemoryEventStoreException(
                sprintf("An event with playhead '%d' is already committed.", $playhead)
            );
        }
    }

    public function visitEvents(Criteria $criteria, EventVisitorInterface $eventVisitor)
    {
        foreach ($this->events as $id => $events) {
            foreach ($events as $event) {
                if (! $criteria->isMatchedBy($event)) {
                    continue;
                }

                $eventVisitor->doWithEvent($event);
            }
        }
    }
}
