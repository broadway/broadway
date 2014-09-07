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

/**
 * Represents entities that are an aggregate root.
 */
interface AggregateRoot
{
    /**
     * @return DomainEventStream
     */
    public function getUncommittedEvents();

    /**
     * @return string
     */
    public function getAggregateRootId();
}
