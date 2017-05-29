<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting\Trigger;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Snapshotting\Trigger;

final class EventCountTrigger implements Trigger
{
    private $count;

    /**
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     *
     * @return bool
     */
    public function shouldTakeSnapshot(EventSourcedAggregateRoot $aggregateRoot)
    {
        $clonedAggregate = clone $aggregateRoot;
        foreach ($clonedAggregate->getUncommittedEvents() as $message) {
            if (($message->getPlayhead() + 1) % $this->count === 0) {
                return true;
            }
        }

        return false;
    }
}
