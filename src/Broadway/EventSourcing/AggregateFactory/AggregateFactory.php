<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;

interface AggregateFactory
{
    /**
     * @param string           $aggregateClass    the FQCN of the Aggregate to create
     * @param DomainEventStream $domainEventStream
     *
     * @return \Broadway\EventSourcing\EventSourcedAggregateRoot
     */
    public function create($aggregateClass, DomainEventStream $domainEventStream);
}
