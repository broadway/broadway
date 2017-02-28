<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use LogicException;
use ReflectionClass;

/**
 * Creates aggregates with reflection without constructor.
 */
final class ReflectionAggregateFactory implements AggregateFactory
{
    /**
     * {@inheritDoc}
     */
    public function create($aggregateClass, DomainEventStream $domainEventStream)
    {
        $class = new ReflectionClass($aggregateClass);
        $aggregate = $class->newInstanceWithoutConstructor();

        if (!$aggregate instanceof EventSourcedAggregateRoot) {
            throw new LogicException(sprintf('Impossible to initialize "%s"', $aggregateClass));
        }

        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
