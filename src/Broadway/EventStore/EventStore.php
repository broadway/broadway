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
use Broadway\EventStore\Exception\DuplicatePlayheadException;

/**
 * Loads and stores events.
 */
interface EventStore
{
    /**
     * @param mixed $id
     *
     * @return DomainEventStreamInterface
     */
    public function load($id);

    /**
     * @param mixed $id
     * @param int   $playhead
     */
    public function loadFromPlayhead($id, $playhead);

    /**
     * @param mixed                      $id
     * @param DomainEventStreamInterface $eventStream
     *
     * @throws DuplicatePlayheadException
     */
    public function append($id, DomainEventStreamInterface $eventStream);
}
