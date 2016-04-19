<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStreamInterface;

/**
 * Loads and stores events.
 */
interface EventStoreInterface
{
    /**
     * @param mixed $id
     *
     * @return DomainEventStreamInterface
     */
    public function load($streamType, $identifier);

    /**
     * @param mixed                      $identifier
     * @param DomainEventStreamInterface $eventStream
     */
    public function append($identifier, DomainEventStreamInterface $eventStream);
}
