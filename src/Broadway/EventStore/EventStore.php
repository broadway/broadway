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

use Broadway\Domain\StreamsDomainEvents;

/**
 * Loads and stores events.
 */
interface EventStore
{
    /**
     * @param mixed $id
     *
     * @return StreamsDomainEvents
     */
    public function load($id);

    /**
     * @param mixed $id
     * @param StreamsDomainEvents $eventStream
     */
    public function append($id, StreamsDomainEvents $eventStream);
}
