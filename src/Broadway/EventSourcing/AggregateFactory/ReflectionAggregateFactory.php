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
use LogicException;
use ReflectionClass;

/**
 * Creates aggregates with reflection without constructor.
 */
final class ReflectionAggregateFactory implements AggregateFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        $class = new ReflectionClass($aggregateClass);
        $aggregate = $class->newInstanceWithoutConstructor();

        if (!$aggregate instanceof EventSourcedAggregateRoot) {
            throw new LogicException(sprintf('Impossible to initialize "%s"', $aggregateClass));
        }

        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
