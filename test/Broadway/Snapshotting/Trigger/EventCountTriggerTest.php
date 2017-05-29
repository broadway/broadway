<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *
 */

namespace Broadway\Snapshotting\Trigger;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Snapshotting\Testing\TriggerTestCase;
use Broadway\Snapshotting\Trigger;

class EventCountTriggerTest extends TriggerTestCase
{

    /**
     * @test
     */
    public function it_should_not_mutate_original_aggregate()
    {
        $aggregate = $this->createAggregateWithEvents(5);
        $this->trigger->shouldTakeSnapshot($aggregate);
        $this->assertCount(5, $aggregate->getUncommittedEvents());
    }

    /**
     * @return EventSourcedAggregateRoot[]
     */
    public function createAggregatesMeetingTriggerConditions()
    {
        return [
            [$this->createAggregateWithEvents(5)],
        ];
    }

    /**
     * @return EventSourcedAggregateRoot[]
     */
    public function createAggregatesFailingTriggerConditions()
    {
        return [
            [$this->createAggregateWithEvents(4)],
        ];
    }

    /**
     * @return Trigger
     */
    protected function createTrigger()
    {
        return new EventCountTrigger(5);
    }

    /**
     * @param int $numberOfEvents
     */
    private function createAggregateWithEvents($numberOfEvents)
    {
        $aggregate = new TestTriggerAggregate();
        for ($i = 0; $i < $numberOfEvents; $i++) {
            $aggregate->apply(new \stdClass());
        }

        return $aggregate;
    }
}

final class TestTriggerAggregate extends EventSourcedAggregateRoot
{

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return 42;
    }
}
