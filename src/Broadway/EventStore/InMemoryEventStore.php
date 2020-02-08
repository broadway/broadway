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
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

/**
 * In-memory implementation of an event store.
 *
 * Useful for testing code that uses an event store.
 */
final class InMemoryEventStore implements EventStore, EventStoreManagement
{
    private $events = [];

    /** @var EventBus|null */
    private $eventBus;

    public function __construct(?EventBus $eventBus = null)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): DomainEventStream
    {
        $id = (string) $id;

        if (isset($this->events[$id])) {
            return new DomainEventStream($this->events[$id]);
        }

        throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
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
    public function loadFromPlayheadToPlayhead($id, int $fromPlayhead, int $toPlayhead): DomainEventStream
    {
        $id = (string) $id;

        if (!isset($this->events[$id])) {
            return new DomainEventStream([]);
        }

        return new DomainEventStream(
            array_values(
                array_filter(
                    $this->events[$id],
                    function ($event) use ($fromPlayhead, $toPlayhead) {
                        return $fromPlayhead <= $event->getPlayhead() && $toPlayhead >= $event->getPlayhead();
                    }
                )
            )
        );
    }
    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStream $eventStream): void
    {
        $id = (string) $id;

        if (!isset($this->events[$id])) {
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
     * @param DomainMessage[] $events
     */
    private function assertStream(array $events, DomainEventStream $eventsToAppend): void
    {
        /** @var DomainMessage $event */
        foreach ($eventsToAppend as $event) {
            $playhead = $event->getPlayhead();

            if (isset($events[$playhead])) {
                throw new DuplicatePlayheadException($eventsToAppend);
            }
        }
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        foreach ($this->events as $id => $events) {
            foreach ($events as $event) {
                if (!$criteria->isMatchedBy($event)) {
                    continue;
                }

                $eventVisitor->doWithEvent($event);
            }
        }
    }

    public function replay($id, int $fromPlayhead, ?int $toPlayhead = null): void
    {
        $eventStream = $this->loadFromPlayheadToPlayhead($id, $fromPlayhead, $toPlayhead);

        if ($this->eventBus) {
            $this->eventBus->publish($eventStream);
        }
    }
}
