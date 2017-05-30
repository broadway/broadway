<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventSourcing\AbstractEventSourcingRepositoryTest;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Repository\Repository;
use Broadway\Snapshotting\Snapshotter\SimpleInterfaceSnapshotter;
use Broadway\Snapshotting\Trigger\EventCountTrigger;

class SnapshottingEventSourcingRepositoryTest extends AbstractEventSourcingRepositoryTest
{
    private $snapshotStore;

    public function setUp()
    {
        $this->snapshotStore = new InMemorySnapshotStore();
        parent::setUp();
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function it_throws_an_exception_when_instantiated_with_a_class_that_is_not_an_EventSourcedAggregateRoot()
    {
        new SnapshottingEventSourcingRepository(
            $this->eventStore,
            $this->eventBus,
            'stdClass',
            new PublicConstructorAggregateFactory(),
            $this->snapshotStore,
            new SimpleInterfaceSnapshotter(),
            new EventCountTrigger(5)
        );
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function it_throws_an_exception_when_instantiated_with_a_class_that_is_not_Snapshotable()
    {
        new SnapshottingEventSourcingRepository(
            $this->eventStore,
            $this->eventBus,
            'Broadway\Snapshotting\TestNonSnapshotableAggregate',
            new PublicConstructorAggregateFactory(),
            $this->snapshotStore,
            new SimpleInterfaceSnapshotter(),
            new EventCountTrigger(5)
        );
    }

    /**
     * @test
     */
    public function it_saves_Snapshot_when_trigger_condition_met()
    {
        $this->repository->save($this->createAggregateWithEvents(5));

        $this->assertEquals(
            new Snapshot(42, 4, ['timesIncremented' => 4]),
            $this->snapshotStore->load(42)
        );
    }

    /**
     * @test
     */
    public function it_reconstitutes_Aggregate_from_available_Snapshot()
    {
        $this->snapshotStore->save(new Snapshot(42, 4, ['timesIncremented' => 4]));

        $aggregate = $this->repository->load(42);
        $expectedAggregate = $this->createAggregateWithEvents(5);
        $expectedAggregate->getUncommittedEvents(); // Flush events

        $this->assertEquals(
            $expectedAggregate,
            $aggregate
        );
    }

    /**
     * @test
     */
    public function it_applies_events_past_Snapshot_playhead()
    {
        $this->snapshotStore->save(new Snapshot(42, 4, ['timesIncremented' => 4]));
        $this->eventStore->append(42, new DomainEventStream(
            [
                DomainMessage::recordNow(42, 5, new Metadata([]), new TestSnapshotIncrementEvent()),
                DomainMessage::recordNow(42, 6, new Metadata([]), new TestSnapshotIncrementEvent()),
            ]
        ));

        $aggregate = $this->repository->load(42);
        $expectedAggregate = $this->createAggregateWithEvents(7);
        $expectedAggregate->getUncommittedEvents(); // Flush events

        $this->assertEquals(
            $expectedAggregate,
            $aggregate
        );
    }

    /**
     * @return Repository
     */
    protected function createEventSourcingRepository(
        TraceableEventStore $eventStore,
        TraceableEventBus $eventBus,
        array $eventStreamDecorators
    ) {
        return new SnapshottingEventSourcingRepository(
            $eventStore,
            $eventBus,
            '\Broadway\Snapshotting\TestSnapshotAggregate',
            new PublicConstructorAggregateFactory(),
            $this->snapshotStore,
            new SimpleInterfaceSnapshotter(),
            new EventCountTrigger(5),
            $eventStreamDecorators
        );
    }

    /**
     * @return EventSourcedAggregateRoot
     */
    protected function createAggregate()
    {
        return new TestSnapshotAggregate();
    }

    private function createAggregateWithEvents($numberOfEvents)
    {
        $aggregate = new TestSnapshotAggregate();
        $aggregate->apply(new TestSnapshotIdProvidingEvent());
        for ($i = 0; $i < ($numberOfEvents - 1); $i++) {
            $aggregate->apply(new TestSnapshotIncrementEvent());
        }

        return $aggregate;
    }

}

final class TestNonSnapshotableAggregate extends EventSourcedAggregateRoot
{

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return 42;
    }
}

final class TestSnapshotAggregate extends EventSourcedAggregateRoot implements Snapshotable
{
    private $id = 1; // base ID to be consistent with other tests
    private $timesIncremented = 0;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getSnapshotPayload()
    {
        return [
            'timesIncremented' => $this->timesIncremented,
        ];
    }

    public function getTimesIncremented()
    {
        return $this->timesIncremented;
    }

    /**
     * @param Snapshot $snapshot
     */
    public function applySnapshot(Snapshot $snapshot)
    {
        $this->id = $snapshot->getId();
        $snapshotPayload = $snapshot->getPayload();
        $this->timesIncremented = $snapshotPayload['timesIncremented'];
    }

    protected function applyTestSnapshotIdProvidingEvent($event)
    {
        $this->id = $event->getId();
    }

    protected function applyTestSnapshotIncrementEvent()
    {
        $this->timesIncremented++;
    }
}

final class TestSnapshotIdProvidingEvent
{
    public function getId()
    {
        return 42;
    }
}

final class TestSnapshotIncrementEvent
{

}
