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

namespace MicroModule\Broadway\EventStore\Management;

use MicroModule\Broadway\Domain\DomainMessage;

final class Criteria
{
    private $aggregateRootTypes = [];
    private $aggregateRootIds = [];
    private $eventTypes = [];

    /**
     * Create a new criteria with the specified aggregate root types.
     *
     * @param string[] $aggregateRootTypes
     */
    public function withAggregateRootTypes(array $aggregateRootTypes): self
    {
        $instance = clone $this;
        $instance->aggregateRootTypes = $aggregateRootTypes;

        return $instance;
    }

    /**
     * Create a new criteria with the specified aggregate root IDs.
     *
     * @param mixed[] $aggregateRootIds
     */
    public function withAggregateRootIds(array $aggregateRootIds): self
    {
        $instance = clone $this;
        $instance->aggregateRootIds = $aggregateRootIds;

        return $instance;
    }

    /**
     * Create a new criteria with the specified event types.
     *
     * @param mixed[] $eventTypes
     */
    public function withEventTypes(array $eventTypes): self
    {
        $instance = clone $this;
        $instance->eventTypes = $eventTypes;

        return $instance;
    }

    /**
     * Get the aggregate root types for the criteria.
     *
     * @return string[]
     */
    public function getAggregateRootTypes(): array
    {
        return $this->aggregateRootTypes;
    }

    /**
     * Get the aggregate root IDs for the criteria.
     *
     * @return mixed[]
     */
    public function getAggregateRootIds(): array
    {
        return $this->aggregateRootIds;
    }

    /**
     * Get the event types for the criteria.
     *
     * @return mixed[]
     */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    /**
     * Create a new criteria.
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * Determine if a domain message is matched by this criteria.
     */
    public function isMatchedBy(DomainMessage $domainMessage): bool
    {
        if ($this->aggregateRootTypes) {
            throw new CriteriaNotSupportedException('Cannot match criteria based on aggregate root types.');
        }

        if ($this->aggregateRootIds && !in_array($domainMessage->getId(), $this->aggregateRootIds)) {
            return false;
        }

        if ($this->eventTypes && !in_array($domainMessage->getType(), $this->eventTypes)) {
            return false;
        }

        return true;
    }
}
