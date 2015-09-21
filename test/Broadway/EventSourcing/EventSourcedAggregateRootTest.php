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

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class EventSourcedAggregateRootTest extends TestCase
{
    /**
     * @test
     */
    public function it_applies_using_an_incrementing_playhead()
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->apply(new AggregateEvent());
        $aggregateRoot->apply(new AggregateEvent());
        $eventStream = $aggregateRoot->getUncommittedEvents();

        $i = 0;
        foreach ($eventStream as $domainMessage) {
            $this->assertEquals($i, $domainMessage->getPlayhead());
            $i++;
        }
        $this->assertEquals(2, $i);
    }

    /**
     * @test
     */
    public function it_sets_internal_playhead_when_initializing()
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->initializeState($this->toDomainEventStream([new AggregateEvent()]));

        $aggregateRoot->apply(new AggregateEvent());

        $eventStream = $aggregateRoot->getUncommittedEvents();
        foreach ($eventStream as $domainMessage) {
            $this->assertEquals(1, $domainMessage->getPlayhead());
        }
    }

    /**
     * @test
     */
    public function it_calls_apply_for_specific_events()
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->initializeState($this->toDomainEventStream([new AggregateEvent()]));

        $this->assertTrue($aggregateRoot->isCalled);
    }

    private function toDomainEventStream(array $events)
    {
        $messages = [];
        $playhead = -1;
        foreach ($events as $event) {
            $playhead++;
            $messages[] = DomainMessage::recordNow(1, $playhead, new Metadata([]), $event);
        }

        return new DomainEventStream($messages);
    }
}

class MyTestAggregateRoot extends EventSourcedAggregateRoot
{
    public $isCalled = false;

    public function getAggregateRootId()
    {
        return 'y0l0';
    }

    public function applyAggregateEvent($event)
    {
        $this->isCalled = true;
    }
}

class AggregateEvent
{
}
