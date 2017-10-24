<?php

declare(strict_types=1);

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;

/**
 * Creates aggregates by instantiating the aggregateClass and then
 * passing a DomainEventStream to the public initializeState() method.
 * E.g. (new \Vendor\AggregateRoot)->initializeState($domainEventStream);.
 */
final class PublicConstructorAggregateFactory implements AggregateFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        $aggregate = new $aggregateClass();
        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
