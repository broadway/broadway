<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Domain;

use ArrayIterator;

/**
 * Implementation of the DomainEventStreamInterface.
 */
class DomainEventStream implements DomainEventStreamInterface
{
    /**
     * @var array
     */
    private $events;

    /**
     * @param array $events
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->events);
    }
}
