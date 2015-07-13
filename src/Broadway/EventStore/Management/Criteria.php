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
    private $aggregateRootTypes = array();
    private $aggregateRootIds = array();
    private $eventTypes = array();

    public function withAggregateRootTypes(array $aggregateRootTypes)
    {
        $instance = clone($this);
        $instance->aggregateRootTypes = $aggregateRootTypes;

        return $instance;
    }

    public function withAggregateRootType($aggregateRootType)
    {
        $instance = clone($this);
        $instance->aggregateRootTypes = array($aggregateRootType);

        return $instance;
    }

    public function withAdditionalAggregateRootType($aggregateRootType)
    {
        $instance = clone($this);
        $instance->aggregateRootTypes[] = $aggregateRootType;

        return $instance;
    }

    public function withAggregateRootIds(array $aggregateRootIds)
    {
        $instance = clone($this);
        $instance->aggregateRootIds = $aggregateRootIds;

        return $instance;
    }

    public function withAggregateRootId($aggregateRootId)
    {
        $instance = clone($this);
        $instance->aggregateRootIds = array($aggregateRootId);

        return $instance;
    }

    public function withAdditionalAggregateRootId($aggregateRootId)
    {
        $instance = clone($this);
        $instance->aggregateRootIds[] = $aggregateRootId;

        return $instance;
    }

    public function withEventTypes(array $eventTypes)
    {
        $instance = clone($this);
        $instance->eventTypes = $eventTypes;

        return $instance;
    }

    public function withEventType($eventType)
    {
        $instance = clone($this);
        $instance->eventTypes = array($eventType);

        return $instance;
    }

    public function withAdditionalEventType($eventType)
    {
        $instance = clone($this);
        $instance->eventTypes[] = $eventType;

        return $instance;
    }

    public function getAggregateRootTypes()
    {
        return $this->aggregateRootTypes;
    }

    public function getAggregateRootIds()
    {
        return $this->aggregateRootIds;
    }

    public function getEventTypes()
    {
        return $this->eventTypes;
    }

    public static function create()
    {
        return new static();
    }

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
