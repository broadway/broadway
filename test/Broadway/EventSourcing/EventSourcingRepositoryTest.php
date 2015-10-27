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
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventSourcing\AggregateFactory\NamedConstructorAggregateFactory;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventStore\TraceableEventStore;

class EventSourcingRepositoryTest extends AbstractEventSourcingRepositoryTest
{
    protected function createEventSourcingRepository(TraceableEventStore $eventStore, TraceableEventBus $eventBus, array $eventStreamDecorators)
    {
        return new EventSourcingRepository($eventStore, $eventBus, '\Broadway\EventSourcing\TestEventSourcedAggregate', new PublicConstructorAggregateFactory(), $eventStreamDecorators);
    }

    protected function createAggregate()
    {
        return new TestEventSourcedAggregate();
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function it_throws_an_exception_when_instantiated_with_a_class_that_is_not_an_EventSourcedAggregateRoot()
    {
        new EventSourcingRepository($this->eventStore, $this->eventBus, 'stdClass', new PublicConstructorAggregateFactory());
    }

    /**
     * @test
     */
    public function it_can_use_an_alternative_AggregateFactory_to_create_the_Aggregate()
    {
        // make sure events exist in the event store
        $id = 'y0l0';
        $this->eventStore->append($id, new DomainEventStream([
            DomainMessage::recordNow(42, 0, new Metadata([]), new DidEvent())
        ]));

        $repository = $this->repositoryWithStaticAggregateFactory();
        $aggregate  = $repository->load('y0l0');
        $this->assertTrue($aggregate->constructorWasCalled);
        $this->assertEquals($aggregate->instantiatedThrough, 'instantiateForReconstitution');

        $repository = $this->repositoryWithStaticAggregateFactory('justAnotherInstantiation');
        $aggregate  = $repository->load('y0l0');
        $this->assertTrue($aggregate->constructorWasCalled);
        $this->assertEquals($aggregate->instantiatedThrough, 'justAnotherInstantiation');
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function it_throws_an_exception_if_the_static_method_does_not_exist()
    {
        // make sure events exist in the event store
        $id = 'y0l0';
        $this->eventStore->append($id, new DomainEventStream([
            DomainMessage::recordNow(42, 0, new Metadata([]), new DidEvent())
        ]));

        $repository = $this->repositoryWithStaticAggregateFactory('someUnknownStaticmethod');
        $repository->load('y0l0');
    }

    protected function repositoryWithStaticAggregateFactory($staticMethod = null)
    {
        if (is_null($staticMethod)) {
            $staticFactory = new NamedConstructorAggregateFactory();
        } else {
            $staticFactory = new NamedConstructorAggregateFactory($staticMethod);
        }

        return new EventSourcingRepository(
            $this->eventStore,
            $this->eventBus,
            '\Broadway\EventSourcing\TestEventSourcedAggregateWithStaticConstructor',
            $staticFactory,
            []
        );
    }
}

class TestEventSourcedAggregate extends EventSourcedAggregateRoot
{
    public $numbers;

    public function getAggregateRootId()
    {
        return 42;
    }

    protected function applyDidNumberEvent($event)
    {
        $this->numbers[] = $event->number;
    }
}

class TestEventSourcedAggregateWithStaticConstructor extends EventSourcedAggregateRoot
{
    public $constructorWasCalled = false;
    public $instantiatedThrough;

    private function __construct($instantiatedThrough)
    {
        $this->constructorWasCalled = true;
        $this->instantiatedThrough  = $instantiatedThrough;
    }

    public function getAggregateRootId()
    {
        return 'y0l0';
    }

    public static function instantiateForReconstitution()
    {
        return new TestEventSourcedAggregateWithStaticConstructor(__FUNCTION__);
    }

    public static function justAnotherInstantiation()
    {
        return new TestEventSourcedAggregateWithStaticConstructor(__FUNCTION__);
    }
}

class DidEvent
{
}
