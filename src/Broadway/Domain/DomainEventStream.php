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
 */
final class DomainEventStream implements \IteratorAggregate
{
    private $events;

    /**
     * @param mixed[] $events
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->events);
    }
}
