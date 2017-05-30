<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting;

use Assert\Assertion as Assert;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\Repository;

class SnapshottingEventSourcingRepository implements Repository
{
    private $eventStore;
    private $eventBus;
    private $aggregateClass;
    private $aggregateFactory;
    private $snapshotStore;
    private $snapshotter;
    private $trigger;
    private $eventStreamDecorators;

    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        $aggregateClass,
        AggregateFactory $aggregateFactory,
        SnapshotStore $snapshotStore,
        Snapshotter $snapshotter,
        Trigger $trigger,
        $eventStreamDecorators = []
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);
        $this->assertImplementsSnapshotable($aggregateClass);

        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->aggregateClass = $aggregateClass;
        $this->aggregateFactory = $aggregateFactory;
        $this->snapshotStore = $snapshotStore;
        $this->snapshotter = $snapshotter;
        $this->trigger = $trigger;
        $this->eventStreamDecorators = $eventStreamDecorators;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        try {
            $snapshot = $this->snapshotStore->load($id);
            if ($snapshot !== null) {
                $domainEventStream = $this->eventStore->loadFromPlayhead($id, $snapshot->getPlayhead());
            } else {
                $domainEventStream = $this->eventStore->load($id);
            }

            return $this->aggregateFactory->create($this->aggregateClass, $domainEventStream, $snapshot);
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

        $shouldTakeSnapshot = $this->trigger->shouldTakeSnapshot($aggregate);

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);

        if ($shouldTakeSnapshot) {
            $this->snapshotStore->save($this->snapshotter->takeSnapshot($aggregate));
        }
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream)
    {
        $aggregateType = $this->getType();
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

    private function assertImplementsSnapshotable($class)
    {
        Assert::implementsInterface(
            $class,
            Snapshotable::class,
            sprintf("Class '%s' does not implement Snapshotable.", $class)
        );
    }

    private function getType()
    {
        return $this->aggregateClass;
    }
}
