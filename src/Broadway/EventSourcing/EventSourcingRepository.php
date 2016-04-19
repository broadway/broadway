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
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\AggregateFactory\AggregateFactoryInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;

/**
 * Naive initial implementation of an event sourced aggregate repository.
 */
class EventSourcingRepository implements RepositoryInterface
{
    private $eventStore;
    private $eventBus;
    private $streamType;
    private $aggregateClass;
    private $eventStreamDecorators = array();
    private $aggregateFactory;

    /**
     * @param EventStoreInterface             $eventStore
     * @param EventBusInterface               $eventBus
     * @param string                          $streamType
     * @param string                          $aggregateClass
     * @param AggregateFactoryInterface       $aggregateFactory
     * @param EventStreamDecoratorInterface[] $eventStreamDecorators
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        $streamType,
        $aggregateClass,
        AggregateFactoryInterface $aggregateFactory,
        array $eventStreamDecorators = array()
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);

        $this->eventStore            = $eventStore;
        $this->eventBus              = $eventBus;
        $this->aggregateClass        = $aggregateClass;
        $this->aggregateFactory      = $aggregateFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->streamType            = $streamType;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        try {
            $domainEventStream = $this->eventStore->load($this->streamType, $id);

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
            'Broadway\EventSourcing\EventSourcedAggregateRoot',
            sprintf("Class '%s' is not an EventSourcedAggregateRoot.", $class)
        );
    }

    private function getType()
    {
        return $this->aggregateClass;
    }
}
