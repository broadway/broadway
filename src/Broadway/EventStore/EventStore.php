<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\Exception\DuplicatePlayheadException;

/**
 * Loads and stores events.
 */
interface EventStore
{
    /**
     * @param mixed $id
     */
    public function load($id): DomainEventStream;

    /**
     * @param mixed $id
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream;

    /**
     * @param mixed $id
     *
     * @throws DuplicatePlayheadException
     */
    public function append($id, DomainEventStream $eventStream): void;
}
