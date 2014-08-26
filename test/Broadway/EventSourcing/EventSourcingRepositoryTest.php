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

use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventStore\TraceableEventStore;

class EventSourcingRepositoryTest extends AbstractEventSourcingRepositoryTest
{
    protected function createEventSourcingRepository(TraceableEventStore $eventStore, TraceableEventBus $eventBus, array $eventStreamDecorators)
    {
        return new EventSourcingRepository($eventStore, $eventBus, '\Broadway\EventSourcing\TestEventSourcedAggregate', $eventStreamDecorators);
    }

    protected function createAggregate()
    {
        return new TestEventSourcedAggregate();
    }

    /**
     * @test
     * @expectedException Assert\InvalidArgumentException
     */
    public function it_throws_an_exception_when_instantiated_with_a_class_that_is_not_an_EventSourcedAggregateRoot()
    {
        new EventSourcingRepository($this->eventStore, $this->eventBus, 'stdClass');
    }
}

class TestEventSourcedAggregate extends EventSourcedAggregateRoot
{
    public $numbers;

    public function getId()
    {
        return 42;
    }

    protected function applyDidNumberEvent($event)
    {
        $this->numbers[] = $event->number;
    }
}
