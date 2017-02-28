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

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\Serializer\Serializable;
use Broadway\TestCase;

abstract class EventStoreManagementTest extends TestCase
{
    /**
     * @var EventStore|EventStoreManagement
     */
    protected $eventStore;

    /**
     * @var DateTime
     */
    protected $now;

    public function setUp()
    {
        $this->now        = DateTime::now();
        $this->eventStore = $this->createEventStore();
        $this->createAndInsertEventFixtures();
        $this->eventVisitor = new RecordingEventVisitor();
    }

    protected function visitEvents(Criteria $criteria = null)
    {
        $eventVisitor = new RecordingEventVisitor();

        $this->eventStore->visitEvents($criteria, $eventVisitor);

        return $eventVisitor->getVisitedEvents();
    }

    abstract protected function createEventStore();

    /** @test */
    public function it_visits_all_events()
    {
        $visitedEvents = $this->visitEvents(Criteria::create());

        $this->assertVisitedEventsArEquals($this->getEventFixtures(), $visitedEvents);
    }

    /** @test */
    public function it_visits_aggregate_root_ids()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()->withAggregateRootIds([
            $this->getId(1),
            $this->getId(3),
        ]));

        $this->assertVisitedEventsArEquals([
            $this->createDomainMessage(1, 0, new Start()),
            $this->createDomainMessage(1, 1, new Middle('a')),
            $this->createDomainMessage(1, 2, new Middle('b')),
            $this->createDomainMessage(1, 3, new Middle('c')),
            $this->createDomainMessage(3, 0, new Start()),
            $this->createDomainMessage(3, 1, new Middle('a')),
            $this->createDomainMessage(3, 2, new Middle('b')),
            $this->createDomainMessage(3, 3, new Middle('c')),
            $this->createDomainMessage(1, 4, new Middle('d')),
            $this->createDomainMessage(3, 4, new Middle('d')),
            $this->createDomainMessage(1, 5, new End()),
            $this->createDomainMessage(3, 5, new End()),
        ], $visitedEvents);
    }

    /** @test */
    public function it_visits_event_types()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()
            ->withEventTypes([
                'Broadway.EventStore.Management.Start',
                'Broadway.EventStore.Management.End',
            ])
        );

        $this->assertVisitedEventsArEquals([
            $this->createDomainMessage(1, 0, new Start()),
            $this->createDomainMessage(2, 0, new Start()),
            $this->createDomainMessage(2, 5, new End()),
            $this->createDomainMessage(3, 0, new Start()),
            $this->createDomainMessage(4, 0, new Start()),
            $this->createDomainMessage(4, 5, new End()),
            $this->createDomainMessage(1, 5, new End()),
            $this->createDomainMessage(3, 5, new End()),
        ], $visitedEvents);
    }

    /**
     * @test
     * @expectedException \Broadway\EventStore\Management\CriteriaNotSupportedException
     */
    public function it_visits_aggregate_root_types()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()
            ->withAggregateRootTypes([
                'Broadway.EventStore.Management.AggregateTypeOne',
                'Broadway.EventStore.Management.AggregateTypeTwo',
            ])
        );
    }

    private function createAndInsertEventFixtures()
    {
        foreach ($this->getEventFixtures() as $domainMessage) {
            $this->eventStore->append($domainMessage->getId(), new DomainEventStream([$domainMessage]));
        }
    }

    /**
     * @return DomainMessage[]
     */
    protected function getEventFixtures()
    {
        return [
            $this->createDomainMessage(1, 0, new Start()),
            $this->createDomainMessage(1, 1, new Middle('a')),
            $this->createDomainMessage(1, 2, new Middle('b')),

            $this->createDomainMessage(2, 0, new Start()),
            $this->createDomainMessage(2, 1, new Middle('a')),
            $this->createDomainMessage(2, 2, new Middle('b')),
            $this->createDomainMessage(2, 3, new Middle('c')),
            $this->createDomainMessage(2, 4, new Middle('d')),
            $this->createDomainMessage(2, 5, new End()),

            $this->createDomainMessage(1, 3, new Middle('c')),

            $this->createDomainMessage(3, 0, new Start()),
            $this->createDomainMessage(3, 1, new Middle('a')),
            $this->createDomainMessage(3, 2, new Middle('b')),
            $this->createDomainMessage(3, 3, new Middle('c')),

            $this->createDomainMessage(1, 4, new Middle('d')),

            $this->createDomainMessage(4, 0, new Start()),
            $this->createDomainMessage(4, 1, new Middle('a')),
            $this->createDomainMessage(4, 2, new Middle('b')),
            $this->createDomainMessage(4, 3, new Middle('c')),
            $this->createDomainMessage(4, 4, new Middle('d')),
            $this->createDomainMessage(4, 5, new End()),

            $this->createDomainMessage(3, 4, new Middle('d')),

            $this->createDomainMessage(1, 5, new End()),

            $this->createDomainMessage(3, 5, new End()),
        ];
    }

    private function createDomainMessage($id, $playhead, $event)
    {
        $id = $this->getId($id);

        return new DomainMessage((string) $id, (string) $playhead, new Metadata([]), $event, $this->now);
    }

    private function getId($id)
    {
        $uuid = sprintf('%08d-%04d-4%03d-%04d-%012d', $id, $id, $id, $id, $id);

        return $uuid;
    }

    private function assertVisitedEventsArEquals(array $expectedEvents, array $actualEvents)
    {
        $this->assertEquals(
            $this->groupEventsByAggregateTypeAndId($expectedEvents),
            $this->groupEventsByAggregateTypeAndId($actualEvents)
        );
    }

    /**
     * @param DomainMessage[] $events
     */
    private function groupEventsByAggregateTypeAndId(array $events)
    {
        $eventsByAggregateTypeAndId = [];
        foreach ($events as $event) {
            $type = $event->getType();
            $id   = $event->getId();

            if (! array_key_exists($type, $eventsByAggregateTypeAndId)) {
                $eventsByAggregateTypeAndId[$type] = [];
            }

            if (! array_key_exists($id, $eventsByAggregateTypeAndId[$type])) {
                $eventsByAggregateTypeAndId[$type][$id] = [];
            }

            $eventsByAggregateTypeAndId[$type][$id][] = $event;
        }

        return $eventsByAggregateTypeAndId;
    }
}

class RecordingEventVisitor implements EventVisitor
{
    /**
     * @var DomainMessage
     */
    private $visitedEvents;

    public function doWithEvent(DomainMessage $domainMessage)
    {
        $this->visitedEvents[] = $domainMessage;
    }

    public function getVisitedEvents()
    {
        return $this->visitedEvents;
    }

    public function clearVisitedEvents()
    {
        $this->visitedEvents = [];
    }
}

class Event implements Serializable
{
    public static function deserialize(array $data)
    {
        return new static();
    }

    public function serialize()
    {
        return [];
    }
}

class Start extends Event
{
}

class Middle extends Event
{
    public $position;
    public function __construct($position)
    {
        $this->position = $position;
    }

    public static function deserialize(array $data)
    {
        return new static($data['position']);
    }

    public function serialize()
    {
        return [
            'position' => $this->position,
        ];
    }
}

class End extends Event
{
}
