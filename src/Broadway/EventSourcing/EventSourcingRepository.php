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
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\Repository;

/**
 * Naive initial implementation of an event sourced aggregate repository.
 */
class EventSourcingRepository implements Repository
{
    private $eventStore;
    private $eventBus;
    private $aggregateClass;
    private $eventStreamDecorators = [];
    private $aggregateFactory;

    /**
     * @param EventStore             $eventStore
     * @param EventBus               $eventBus
     * @param string                 $aggregateClass
     * @param AggregateFactory       $aggregateFactory
     * @param EventStreamDecorator[] $eventStreamDecorators
     */
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        $aggregateClass,
        AggregateFactory $aggregateFactory,
        array $eventStreamDecorators = []
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);

        $this->eventStore            = $eventStore;
        $this->eventBus              = $eventBus;
        $this->aggregateClass        = $aggregateClass;
        $this->aggregateFactory      = $aggregateFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        try {
            $domainEventStream = $this->eventStore->load($id);

            return $this->aggregateFactory->create($this->aggregateClass, $domainEventStream);
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($id, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(AggregateRoot $aggregate)
    {
        // maybe we can get generics one day.... ;)
        Assert::isInstanceOf($aggregate, $this->aggregateClass);

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream       = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream)
    {
        $aggregateType       = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }

    private function assertExtendsEventSourcedAggregateRoot($class)
    {
        Assert::subclassOf(
            $class,
            EventSourcedAggregateRoot::class,
            sprintf("Class '%s' is not an EventSourcedAggregateRoot.", $class)
        );
    }

    private function getType()
    {
        return $this->aggregateClass;
    }
}
