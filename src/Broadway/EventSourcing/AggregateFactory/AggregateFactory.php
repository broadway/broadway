<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;
use Broadway\Snapshotting\Snapshot;

interface AggregateFactory
{
    /**
     * @param string            $aggregateClass the FQCN of the Aggregate to create
     * @param DomainEventStream $domainEventStream
     * @param Snapshot          $snapshot the Snapshot to speed up the reconstitution process
     *
     * @return \Broadway\EventSourcing\EventSourcedAggregateRoot
     */
    public function create($aggregateClass, DomainEventStream $domainEventStream, Snapshot $snapshot = null);
}
