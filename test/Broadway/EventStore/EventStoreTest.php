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
use Rhumsaa\Uuid\Uuid;

abstract class EventStoreTest extends TestCase
{
    const STREAM_TYPE = 'MyAggregate';

    protected $eventStore;

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_creates_a_new_entry_when_id_is_new($id)
    {
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ));

        $this->eventStore->append(self::STREAM_TYPE, $id, $domainEventStream);

        $this->assertEquals($domainEventStream, $this->eventStore->load(self::STREAM_TYPE, $id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_appends_to_an_already_existing_stream($id)
    {
        $dateTime          = DateTime::fromString('2014-03-12T14:17:19.176169+00:00');
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ));
        $this->eventStore->append(self::STREAM_TYPE, $id, $domainEventStream);
        $appendedEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),

        ));

        $this->eventStore->append(self::STREAM_TYPE, $id, $appendedEventStream);

        $expected = new DomainEventStream(array(
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ));
        $this->assertEquals($expected, $this->eventStore->load(self::STREAM_TYPE, $id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException Broadway\EventStore\EventStreamNotFoundException
     */
    public function it_throws_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate($id)
    {
        $this->eventStore->load(self::STREAM_TYPE, $id);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException Broadway\EventStore\EventStreamNotFoundException
     */
    public function it_throws_when_loading_a_stream_for_a_different_stream_type($id)
    {
        $dateTime          = DateTime::fromString('2014-03-12T14:17:19.176169+00:00');
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ));
        $this->eventStore->append(self::STREAM_TYPE, $id, $domainEventStream);

        $this->eventStore->load('SomeOtherStream', $id);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException Broadway\EventStore\EventStoreException
     */
    public function it_throws_an_exception_when_appending_a_duplicate_playhead($id)
    {
        $domainMessage     = $this->createDomainMessage($id, 0);
        $baseStream        = new DomainEventStream(array($domainMessage));
        $this->eventStore->append(self::STREAM_TYPE, $id, $baseStream);
        $appendedEventStream = new DomainEventStream(array($domainMessage));

        $this->eventStore->append(self::STREAM_TYPE, $id, $appendedEventStream);
    }

    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage Object of class Broadway\EventStore\IdentityThatCannotBeConvertedToAString could not be converted to string
     */
    public function it_throws_an_exception_when_an_id_cannot_be_converted_to_a_string()
    {
        $id = new IdentityThatCannotBeConvertedToAString(
            'Yolntbyaac' //You only live nine times because you are a cat
        );

        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ));

        $this->eventStore->append(self::STREAM_TYPE, $id, $domainEventStream);
    }

    public function idDataProvider()
    {
        $uuid = Uuid::uuid4();

        return array(
            'Simple String' => array(
                'Yolntbyaac', //You only live nine times because you are a cat
            ),
            'Identitiy' => array(
                new StringIdentity(
                    'Yolntbyaac' //You only live nine times because you are a cat
                ),
            ),
            'Integer' => array(
                42, // test an int
            ),
            'UUID String' => array(
                $uuid->toString(), // test UUID
            ),
        );
    }

    protected function createDomainMessage($id, $playhead, $recordedOn = null)
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

class StringIdentity
{
    private $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}

class IdentityThatCannotBeConvertedToAString
{
    private $id;
    public function __construct($id)
    {
        $this->id = $id;
    }
}
