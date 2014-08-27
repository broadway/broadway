<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing;

use Assert\Assertion as Assert;
use Assert\InvalidArgumentException;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\PublishesEvents;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\StoresAggregates;

/**
 * Naive initial implementation of an event sourced aggregate repository.
 */
class EventSourcingRepository implements StoresAggregates
{
    private $eventStore;
    private $eventBus;
    private $aggregateClass;

    /**
     * @param string $aggregateClass
     */
    public function __construct(
        EventStore $eventStore,
        PublishesEvents $eventBus,
        $aggregateClass,
        array $eventStreamDecorators = array()
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);

        $this->eventStore            = $eventStore;
        $this->eventBus              = $eventBus;
        $this->aggregateClass        = $aggregateClass; // todo: aggregate factory
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        try {
            $domainEventStream = $this->eventStore->load($id);

            $aggregate = new $this->aggregateClass();
            $aggregate->initializeState($domainEventStream);

            return $aggregate;
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($id, $e);
        }
    }

    public function add(AggregateRoot $aggregate)
    {
        // maybe we can get generics one day.... ;)
        Assert::isInstanceOf($aggregate, $this->aggregateClass);

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream       = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getId(), $eventStream);
        $this->eventBus->publish($domainEventStream);
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream)
    {
        $aggregateType       = $this->getType();
        $aggregateIdentifier = $aggregate->getId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }

    // todo: move to assert lib?
    private function assertExtendsEventSourcedAggregateRoot($class)
    {
        $parents = class_parents($class);

        if (! in_array('Broadway\EventSourcing\EventSourcedAggregateRoot', $parents)) {
            throw new InvalidArgumentException(sprintf("Class '%s' is not an EventSourcedAggregateRoot.", $class), -1);
        }
    }

    private function getType()
    {
        return $this->aggregateClass;
    }
}
