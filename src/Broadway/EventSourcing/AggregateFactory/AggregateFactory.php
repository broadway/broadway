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

namespace MicroModule\Broadway\EventSourcing\AggregateFactory;

use MicroModule\Broadway\Domain\DomainEventStream;
use MicroModule\Broadway\EventSourcing\EventSourcedAggregateRoot;

interface AggregateFactory
{
    /**
     * @param string $aggregateClass the FQCN of the Aggregate to create
     */
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot;
}
