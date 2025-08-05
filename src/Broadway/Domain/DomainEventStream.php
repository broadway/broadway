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

namespace Broadway\Domain;

/**
 * Represents a stream of DomainEventMessages in sequence.
 *
 * @template T
 *
 * @template-implements \IteratorAggregate<int, T>
 */
final readonly class DomainEventStream implements \IteratorAggregate
{

    /**
     * @param array<T> $events
     */
    public function __construct(private array $events)
    {
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->events);
    }
}
