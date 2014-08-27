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
use Broadway\Domain\StreamsDomainEvents;

/**
 * In-memory implementation of an event store.
 *
 * Useful for testing code that uses an event store.
 */
class InMemoryEventStore implements EventStore
{
    private $events = array();

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        if (isset($this->events[$id])) {
            return new DomainEventStream($this->events[$id]);
        }

        throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, StreamsDomainEvents $eventStream)
    {
        if (! isset($this->events[$id])) {
            $this->events[$id] = array();
        }

        foreach ($eventStream as $event) {
            $playhead = $event->getPlayhead();
            $this->assertPlayhead($this->events[$id], $playhead);

            $this->events[$id][$playhead] = $event;
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
}
