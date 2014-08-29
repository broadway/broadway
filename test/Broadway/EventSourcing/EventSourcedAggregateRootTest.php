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

use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class EventSourcedAggregateRootTest extends TestCase
{
    /**
     * @test
     */
    public function apply_should_use_an_incrementing_playhead()
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
    public function initialize_state_should_set_internal_playhead()
    {
        $aggregateRoot = MyTestAggregateRoot::reconstituteFromDomainEventStream(
            $this->toDomainEventStream(array(new AggregateEvent()))
        );

        $aggregateRoot->apply(new AggregateEvent());

        $eventStream = $aggregateRoot->getUncommittedEvents();
        foreach ($eventStream as $domainMessage) {
            $this->assertEquals(1, $domainMessage->getPlayhead());
        }
    }

    /**
     * @test
     */
    public function apply_should_call_the_apply_for_specific_event()
    {
        $aggregateRoot = MyTestAggregateRoot::reconstituteFromDomainEventStream(
            $this->toDomainEventStream(array(new AggregateEvent()))
        );

        $this->assertTrue($aggregateRoot->isCalled);
    }

    /**
     * @test
     */
    public function protected_constructors_should_be_called()
    {
        $aggregateRoot = MyTestAggregateRootWithProtectedConstructor::reconstituteFromDomainEventStream(
            $this->toDomainEventStream(array())
        );

        $this->assertTrue($aggregateRoot->constructorWasCalled);
    }

    /**
     * @test
     */
    public function private_constructors_should_be_called()
    {
        $aggregateRoot = MyTestAggregateRootWithPrivateConstructor::reconstituteFromDomainEventStream(
            $this->toDomainEventStream(array())
        );

        $this->assertTrue($aggregateRoot->constructorWasCalled);
    }

    private function toDomainEventStream(array $events)
    {
        $messages = array();
        $playhead = -1;
        foreach ($events as $event) {
            $playhead++;
            $messages[] = DomainMessage::recordNow(1, $playhead, new Metadata(array()), $event);
        }

        return new DomainEventStream($messages);
    }
}

class MyTestAggregateRoot extends EventSourcedAggregateRoot
{
    public $isCalled = false;

    public function getId()
    {
        return 'y0l0';
    }

    public function applyAggregateEvent($event)
    {
        $this->isCalled = true;
    }
}

class MyTestAggregateRootWithProtectedConstructor extends EventSourcedAggregateRoot
{
    public $constructorWasCalled = false;

    protected function __construct()
    {
        $this->constructorWasCalled = true;
    }

    public function getId()
    {
        return 'y0l0';
    }
}

class MyTestAggregateRootWithPrivateConstructor extends EventSourcedAggregateRoot
{
    public $constructorWasCalled = false;

    private function __construct()
    {
        $this->constructorWasCalled = true;
    }

    public function getId()
    {
        return 'y0l0';
    }

    protected static function instantiateForReconstitution()
    {
        return new static();
    }
}

class AggregateEvent
{
}
