<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Serializer\SerializableInterface;
use Broadway\TestCase;

abstract class EventStoreTest extends TestCase
{
    protected $eventStore;

    /**
     * @test
     */
    public function it_should_create_a_new_entry_when_id_is_new()
    {
        $id = 'Yolntbyaac'; //You only live nine times because you are a cat
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ));

        $this->eventStore->append($id, $domainEventStream);

        $this->assertEquals($domainEventStream, $this->eventStore->load($id));
    }

    /**
     * @test
     */
    public function it_should_append_to_an_already_existing_stream()
    {
        $id       = 'Yolntbyaac';
        $dateTime = DateTime::fromString('2014-03-12T14:17:19.176169+00:00');
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ));
        $this->eventStore->append($id, $domainEventStream);
        $appendedEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),

        ));

        $this->eventStore->append($id, $appendedEventStream);

        $expected = new DomainEventStream(array(
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ));
        $this->assertEquals($expected, $this->eventStore->load($id));
    }

    /**
     * @test
     * @expectedException Broadway\EventStore\EventStreamNotFoundException
     */
    public function it_should_throw_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate()
    {
        $id = 'Yolntbyaac'; //You only live nine times because you are a cat
        $this->eventStore->load($id);
    }

    /**
     * @test
     * @expectedException Broadway\EventStore\EventStoreException
     */
    public function it_should_throw_an_exception_when_appending_a_duplicate_playhead()
    {
        $id                = 'Yolntbyaac';
        $domainMessage     = $this->createDomainMessage($id, 0);
        $baseStream        = new DomainEventStream(array($domainMessage));
        $this->eventStore->append($id, $baseStream);
        $appendedEventStream = new DomainEventStream(array($domainMessage));

        $this->eventStore->append($id, $appendedEventStream);
    }

    private function createDomainMessage($id, $playhead, $recordedOn = null)
    {
        return new DomainMessage($id, $playhead, new MetaData(array()), new Event(), $recordedOn ? $recordedOn : DateTime::now());
    }
}

class Event implements SerializableInterface
{
    public static function deserialize(array $data)
    {
        return new Event();
    }

    public function serialize()
    {
        return array();
    }
}
