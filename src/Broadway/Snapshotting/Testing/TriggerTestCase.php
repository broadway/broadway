<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting\Testing;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Snapshotting\Trigger;
use Broadway\TestCase;

abstract class TriggerTestCase extends TestCase
{
    /**
     * @var Trigger
     */
    protected $trigger;

    /**
     * @test
     * @dataProvider createAggregatesMeetingTriggerConditions
     */
    public final function it_triggers_when_Aggregate_meets_trigger_condition($aggregate)
    {
        $this->assertTrue($this->trigger->shouldTakeSnapshot($aggregate));
    }

    /**
     * @test
     * @dataProvider createAggregatesFailingTriggerConditions
     */
    public final function it_does_not_trigger_when_Aggregate_fails_trigger_condition($aggregate)
    {
        $this->assertFalse($this->trigger->shouldTakeSnapshot($aggregate));
    }

    /**
     * @return EventSourcedAggregateRoot[]
     */
    public abstract function createAggregatesMeetingTriggerConditions();

    /**
     * @return EventSourcedAggregateRoot[]
     */
    public abstract function createAggregatesFailingTriggerConditions();

    protected function setUp()
    {
        $this->trigger = $this->createTrigger();
    }

    /**
     * @return Trigger
     */
    protected abstract function createTrigger();
}
