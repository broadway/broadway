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

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\Exception\DuplicatePlayheadException;

/**
 * Loads and stores events.
 *
 * @template T of mixed
 */
interface EventStore
{
    /**
     * @phpstan-param T $id
     */
    public function load(mixed $id): DomainEventStream;

    /**
     * @phpstan-param T $id
     */
    public function loadFromPlayhead(mixed $id, int $playhead): DomainEventStream;

    /**
     * @phpstan-param T $id
     *
     * @throws DuplicatePlayheadException
     */
    public function append(mixed $id, DomainEventStream $eventStream): void;
}
