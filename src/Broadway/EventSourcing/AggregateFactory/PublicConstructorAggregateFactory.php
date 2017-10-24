<?php

declare(strict_types=1);

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;

/**
 * Creates aggregates by instantiating the aggregateClass and then
 * passing a DomainEventStream to the public initializeState() method.
 * E.g. (new \Vendor\AggregateRoot)->initializeState($domainEventStream);
 */
final class PublicConstructorAggregateFactory implements AggregateFactory
{
    /**
     * {@inheritDoc}
     */
    public function create($aggregateClass, DomainEventStream $domainEventStream)
    {
        $aggregate = new $aggregateClass();
        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
