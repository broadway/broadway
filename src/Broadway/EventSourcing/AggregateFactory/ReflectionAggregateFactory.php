<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use LogicException;
use ReflectionClass;

/**
 * Creates aggregates with reflection without constructor.
 */
final class ReflectionAggregateFactory implements AggregateFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream)
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
