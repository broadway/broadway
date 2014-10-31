<?php

namespace Broadway\EventStore\Management;

use Broadway\EventStore\EventVisitorInterface;

interface EventStoreManagementInterface
{
    /**
     * Loads all events available in the event store that match the given <code>criteria</code> and calls {@link
     * EventVisitor#doWithEvent(org.axonframework.domain.DomainEventMessage)} for each event found. Events of a single
     * aggregate are guaranteed to be ordered by their sequence number.
     * <p/>
     * Implementations are encouraged, though not required, to supply events in the absolute chronological order.
     * <p/>
     * Processing stops when the visitor throws an exception.
     *
     * @param EventVisitorInterface $visitor receives each loaded event
     * @param CriteriaInterface $criteria criteria describing the events to select
     * @return void
     */
    public function visitEvents(EventVisitorInterface $visitor, CriteriaInterface $criteria = null);

    /**
     * Returns a CriteriaBuilder that allows the construction of criteria for this EventStore implementation.
     *
     * @return CriteriaBuilderInterface a builder to create Criteria for this Event Store.
     */
    public function newCriteriaBuilder();
}
