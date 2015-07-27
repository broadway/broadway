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
use Broadway\Domain\DomainMessage;

/**
 * Loads and stores events.
 */
interface EventStoreInterface
{
    /**
     * @param mixed $id
     *
     * @return DomainEventStreamInterface|DomainMessage[]
     */
    public function load($id);

    /**
     * @param mixed                                      $id
     * @param DomainEventStreamInterface|DomainMessage[] $eventStream
     */
    public function append($id, DomainEventStreamInterface $eventStream);
}
