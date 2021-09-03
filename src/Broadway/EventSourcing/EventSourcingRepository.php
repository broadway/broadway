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

namespace MicroModule\Broadway\EventSourcing;

use Assert\Assertion as Assert;
use MicroModule\Broadway\Domain\AggregateRoot;
use MicroModule\Broadway\Domain\DomainEventStream;
use MicroModule\Broadway\EventHandling\EventBus;
use MicroModule\Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use MicroModule\Broadway\EventStore\EventStore;
use MicroModule\Broadway\EventStore\EventStreamNotFoundException;
use MicroModule\Broadway\Repository\AggregateNotFoundException;
use MicroModule\Broadway\Repository\Repository;

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
     * @param EventStreamDecorator[] $eventStreamDecorators
     */
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        string $aggregateClass,
        AggregateFactory $aggregateFactory,
        array $eventStreamDecorators = []
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);

        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->aggregateClass = $aggregateClass;
        $this->aggregateFactory = $aggregateFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): AggregateRoot
    {
        try {
            $domainEventStream = $this->eventStore->load($id);

            return $this->aggregateFactory->create($this->aggregateClass, $domainEventStream);
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($id, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(AggregateRoot $aggregate): void
    {
        // maybe we can get generics one day.... ;)
        Assert::isInstanceOf($aggregate, $this->aggregateClass);

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream): DomainEventStream
    {
        $aggregateType = get_class($aggregate);
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }

    private function assertExtendsEventSourcedAggregateRoot(string $class): void
    {
        Assert::subclassOf(
            $class,
            EventSourcedAggregateRoot::class,
            sprintf("Class '%s' is not an EventSourcedAggregateRoot.", $class)
        );
    }
}
