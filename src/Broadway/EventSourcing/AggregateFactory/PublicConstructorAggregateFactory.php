<?php namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStreamInterface;

/**
 * Creates aggregates by instantiating the aggregateClass and then
 * passing a DomainEventStream to the public initializeState() method.
 * E.g. (new \Vendor\AggregateRoot)->initializeState($domainEventStream);
 */
class PublicConstructorAggregateFactory implements AggregateFactory
{
    /** {@inheritDoc} */
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream)
    {
        $aggregate = new $aggregateClass;
        $aggregate->initializeState($domainEventStream);
        return $aggregate;
    }
}
