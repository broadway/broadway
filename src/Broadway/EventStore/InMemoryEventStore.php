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
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagementInterface;

/**
 * In-memory implementation of an event store.
 *
 * Useful for testing code that uses an event store.
 */
class InMemoryEventStore implements EventStoreInterface, EventStoreManagementInterface
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
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        $id = (string) $id;

        if (! isset($this->events[$id])) {
            $this->events[$id] = [];
        }

        /** @var DomainMessage $event */
        foreach ($eventStream as $event) {
            $playhead = $event->getPlayhead();
            $this->assertPlayhead($this->events[$id], $playhead);

            $this->events[$id][$playhead] = $event;
        }
    }

    /**
     * @param array $events
     * @param int $playhead
     */
    private function assertPlayhead(array $events, $playhead)
    {
        if (isset($events[$playhead])) {
            throw new InMemoryEventStoreException(
                sprintf("An event with playhead '%d' is already committed.", $playhead)
            );
        }
    }

    /**
     * @param Criteria $criteria
     * @param EventVisitorInterface $eventVisitor
     */
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
