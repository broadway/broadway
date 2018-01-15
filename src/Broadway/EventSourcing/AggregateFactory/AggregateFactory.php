<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;

interface AggregateFactory
{
    /**
     * @param string            $aggregateClass    the FQCN of the Aggregate to create
     * @param DomainEventStream $domainEventStream
     *
     * @return EventSourcedAggregateRoot
     */
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot;
}
