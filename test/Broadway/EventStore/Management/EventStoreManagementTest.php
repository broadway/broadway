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
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventVisitorInterface;
use Broadway\EventStore\Management\EventStoreManagementInterface;
use Broadway\Serializer\SerializableInterface;
use Broadway\TestCase;

abstract class EventStoreManagementTest extends TestCase
{
    const STREAM_TYPE = 'Management';
    const OTHER_STREAM_TYPE = 'Managementv2';

    /**
     * @var EventStoreInterface|EventStoreManagementInterface
     */
    protected $eventStore;

    /**
     * @var DateTime
     */
    protected $now;

    public function setUp()
    {
        $this->now = DateTime::now();
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

        $expectedEvents = $this->getEventFixtures();

        $this->assertVisitedEventsArEquals($expectedEvents, $visitedEvents);
    }

    /** @test */
    public function it_visits_aggregate_root_ids()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()->withAggregateRootIds(array(
            $this->getId(1),
            $this->getId(3),
        )));

        $this->assertVisitedEventsArEquals(array(
            $this->createDomainMessage(self::STREAM_TYPE, 1, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 5, new End()),
        ), $visitedEvents);
    }

    /** @test */
    public function it_visits_event_types()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()
            ->withEventTypes(array(
                'Broadway.EventStore.Management.Start',
                'Broadway.EventStore.Management.End',
            ))
        );

        $this->assertVisitedEventsArEquals(array(
            $this->createDomainMessage(self::STREAM_TYPE, 1, 0, new Start()),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 0, new Start()),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 5, new End()),
        ), $visitedEvents);
    }

    /** @test */
    public function it_visits_stream_types()
    {
        $visitedEvents = $this->visitEvents(Criteria::create()
            ->withStreamTypes([
                'Management',
            ])
        );

        $this->assertVisitedEventsArEquals(array(
            $this->createDomainMessage(self::STREAM_TYPE, 1, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 5, new End()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 5, new End()),
        ), $visitedEvents);
    }

    private function createAndInsertEventFixtures()
    {
        foreach ($this->getEventFixtures() as $domainMessage) {
            $this->eventStore->append($domainMessage->getId(), new DomainEventStream(array($domainMessage)));
        }
    }

    /**
     * @return DomainMessage[]
     */
    protected function getEventFixtures()
    {
        return array(
            $this->createDomainMessage(self::STREAM_TYPE, 1, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 1, 2, new Middle('b')),

            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 0, new Start()),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 1, new Middle('a')),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 2, new Middle('b')),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 3, new Middle('c')),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 4, new Middle('d')),
            $this->createDomainMessage(self::OTHER_STREAM_TYPE, 2, 5, new End()),

            $this->createDomainMessage(self::STREAM_TYPE, 1, 3, new Middle('c')),

            $this->createDomainMessage(self::STREAM_TYPE, 3, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 3, 3, new Middle('c')),

            $this->createDomainMessage(self::STREAM_TYPE, 1, 4, new Middle('d')),

            $this->createDomainMessage(self::STREAM_TYPE, 4, 0, new Start()),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 1, new Middle('a')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 2, new Middle('b')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 3, new Middle('c')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 4, new Middle('d')),
            $this->createDomainMessage(self::STREAM_TYPE, 4, 5, new End()),

            $this->createDomainMessage(self::STREAM_TYPE, 3, 4, new Middle('d')),

            $this->createDomainMessage(self::STREAM_TYPE, 1, 5, new End()),

            $this->createDomainMessage(self::STREAM_TYPE, 3, 5, new End()),
        );
    }

    private function createDomainMessage($streamType, $id, $playhead, $event)
    {
        $id = $this->getId($id);

        return new DomainMessage($streamType, (string) $id, (int) $playhead, new Metadata(array()), $event, $this->now);
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
        $eventsByAggregateTypeAndId = array();
        foreach ($events as $event) {
            $type = $event->getType();
            $id = $event->getId();

            if (! array_key_exists($type, $eventsByAggregateTypeAndId)) {
                $eventsByAggregateTypeAndId[$type] = array();
            }

            if (! array_key_exists($id, $eventsByAggregateTypeAndId[$type])) {
                $eventsByAggregateTypeAndId[$type][$id] = array();
            }

            $eventsByAggregateTypeAndId[$type][$id][] = $event;
        }

        return $eventsByAggregateTypeAndId;
    }
}

class RecordingEventVisitor implements EventVisitorInterface
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
        $this->visitedEvents = array();
    }
}

class Event implements SerializableInterface
{
    public static function deserialize(array $data)
    {
        return new static();
    }

    public function serialize()
    {
        return array();
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
        return array(
            'position' => $this->position,
        );
    }
}

class End extends Event
{
}
