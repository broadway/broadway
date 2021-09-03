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

namespace Broadway\EventSourcing;

use Broadway\Domain\AggregateRoot as AggregateRootInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;

/**
 * Convenience base class for event sourced aggregate roots.
 */
abstract class EventSourcedAggregateRoot implements AggregateRootInterface
{
    protected array $uncommittedEvents = [];
    protected int $playhead = -1; // 0-based playhead allows events[0] to contain playhead 0

    /**
     * Applies an event. The event is added to the AggregateRoot's list of uncommitted events.
     *
     * @param mixed $event
     */
    public function apply($event): void
    {
        $this->handleRecursively($event);

        ++$this->playhead;
        $this->uncommittedEvents[] = DomainMessage::recordNow(
            $this->getAggregateRootId(),
            $this->playhead,
            new Metadata([]),
            $event
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUncommittedEvents(): DomainEventStream
    {
        $stream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = [];

        return $stream;
    }

    /**
     * Initializes the aggregate using the given "history" of events.
     */
    public function initializeState(DomainEventStream $stream): void
    {
        foreach ($stream as $message) {
            ++$this->playhead;
            $this->handleRecursively($message->getPayload());
        }
    }

    /**
     * Handles event if capable.
     *
     * @param mixed $event
     */
    protected function handle($event): void
    {
        $method = $this->getApplyMethod($event);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($event);
    }

    /**
     * @param mixed $event
     */
    protected function handleRecursively($event): void
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this);
            $entity->handleRecursively($event);
        }
    }

    /**
     * Returns all child entities.
     *
     * Override this method if your aggregate root contains child entities.
     *
     * @return EventSourcedEntity[]
     */
    protected function getChildEntities(): array
    {
        return [];
    }

    /**
     * @param mixed $event
     */
    private function getApplyMethod($event): string
    {
        $classParts = explode('\\', get_class($event));

        return 'apply'.end($classParts);
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}
