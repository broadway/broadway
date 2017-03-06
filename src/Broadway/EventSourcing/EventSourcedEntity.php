<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing;

/**
 * Interface representing event sourced entities.
 */
interface EventSourcedEntity
{
    /**
     * Recursively handles $event
     *
     * @param $event
     */
    public function handleRecursively($event);

    /**
     * Registers aggregateRoot as this EventSourcedEntity's aggregate root
     *
     * @param EventSourcedAggregateRoot $aggregateRoot
     *
     * @throws AggregateRootAlreadyRegisteredException
     */
    public function registerAggregateRoot(EventSourcedAggregateRoot $aggregateRoot);
}
