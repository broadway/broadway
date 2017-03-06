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
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

/**
 * In-memory implementation of an event store.
 *
 * Useful for testing code that uses an event store.
 */
class InMemoryEventStore implements EventStore, EventStoreManagement
{
    private $events = [];

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $id = (string) $id;

        if (isset($this->events[$id])) {
            return new DomainEventStream($this->events[$id]);
        }

        throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
    }

    /**
     * {@inheritDoc}
     */
    public function loadFromPlayhead($id, $playhead)
    {
        $id = (string) $id;

        if (!isset($this->events[$id])) {
            return new DomainEventStream([]);
        }

        return new DomainEventStream(
            array_values(
                array_filter(
                    $this->events[$id],
                    function ($event) use ($playhead) {
                        return $playhead <= $event->getPlayhead();
                    }
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, DomainEventStream $eventStream)
    {
        $id = (string) $id;

        if (! isset($this->events[$id])) {
            $this->events[$id] = [];
        }

        $this->assertStream($this->events[$id], $eventStream);

        /** @var DomainMessage $event */
        foreach ($eventStream as $event) {
            $playhead = $event->getPlayhead();

            $this->events[$id][$playhead] = $event;
        }
    }

    /**
     * @param DomainMessage[]   $events
     * @param DomainEventStream $eventsToAppend
     */
    private function assertStream($events, $eventsToAppend)
    {
        /** @var DomainMessage $event */
        foreach ($eventsToAppend as $event) {
            $playhead = $event->getPlayhead();

            if (isset($events[$playhead])) {
                throw new DuplicatePlayheadException($eventsToAppend);
            }
        }
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor)
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
