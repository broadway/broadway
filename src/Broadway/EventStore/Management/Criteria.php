<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore\Management;

use Broadway\Domain\DomainMessage;

class Criteria
{
    private $aggregateRootTypes = [];
    private $aggregateRootIds   = [];
    private $eventTypes         = [];

    /**
     * Create a new criteria with the specified aggregate root types
     *
     * @param  array  $aggregateRootTypes
     * @return static
     */
    public function withAggregateRootTypes(array $aggregateRootTypes)
    {
        $instance                     = clone($this);
        $instance->aggregateRootTypes = $aggregateRootTypes;

        return $instance;
    }

    /**
     * Create a new criteria with the specified aggregate root IDs
     *
     * @param  array    $aggregateRootIds
     * @return Criteria
     */
    public function withAggregateRootIds(array $aggregateRootIds)
    {
        $instance                   = clone($this);
        $instance->aggregateRootIds = $aggregateRootIds;

        return $instance;
    }

    /**
     * Create a new criteria with the specified event types
     *
     * @param  array    $eventTypes
     * @return Criteria
     */
    public function withEventTypes(array $eventTypes)
    {
        $instance             = clone($this);
        $instance->eventTypes = $eventTypes;

        return $instance;
    }

    /**
     * Get the aggregate root types for the criteria
     *
     * @return string[]
     */
    public function getAggregateRootTypes()
    {
        return $this->aggregateRootTypes;
    }

    /**
     * Get the aggregate root IDs for the criteria
     *
     * @return array
     */
    public function getAggregateRootIds()
    {
        return $this->aggregateRootIds;
    }

    /**
     * Get the event types for the criteria
     *
     * @return array
     */
    public function getEventTypes()
    {
        return $this->eventTypes;
    }

    /**
     * Create a new criteria
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Determine if a domain message is matched by this criteria
     *
     * @param  DomainMessage $domainMessage
     * @return bool
     */
    public function isMatchedBy(DomainMessage $domainMessage)
    {
        if ($this->aggregateRootTypes) {
            throw new CriteriaNotSupportedException(
                'Cannot match criteria based on aggregate root types.'
            );
        }

        if ($this->aggregateRootIds && ! in_array($domainMessage->getId(), $this->aggregateRootIds)) {
            return false;
        }

        if ($this->eventTypes && ! in_array($domainMessage->getType(), $this->eventTypes)) {
            return false;
        }

        return true;
    }
}
