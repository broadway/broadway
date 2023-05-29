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
 * Creates aggregates with reflection without constructor.
 */
final class ReflectionAggregateFactory implements AggregateFactory
{
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        $class = new \ReflectionClass($aggregateClass);
        $aggregate = $class->newInstanceWithoutConstructor();

        if (!$aggregate instanceof EventSourcedAggregateRoot) {
            throw new \LogicException(sprintf('Impossible to initialize "%s"', $aggregateClass));
        }

        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
