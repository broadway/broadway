<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        $aggregate = new $aggregateClass();
        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
