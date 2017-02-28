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
use Broadway\Serializer\Serializable;
use Broadway\TestCase;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;

abstract class EventStoreTest extends TestCase
{
    /** @var EventStore */
    protected $eventStore;

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_creates_a_new_entry_when_id_is_new($id)
    {
        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ]);

        $this->eventStore->append($id, $domainEventStream);

        $this->assertEquals($domainEventStream, $this->eventStore->load($id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_appends_to_an_already_existing_stream($id)
    {
        $dateTime          = DateTime::fromString('2014-03-12T14:17:19.176169+00:00');
        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ]);
        $this->eventStore->append($id, $domainEventStream);
        $appendedEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),

        ]);

        $this->eventStore->append($id, $appendedEventStream);

        $expected = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ]);
        $this->assertEquals($expected, $this->eventStore->load($id));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException \Broadway\EventStore\EventStreamNotFoundException
     */
    public function it_throws_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate($id)
    {
        $this->eventStore->load($id);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     * @expectedException Broadway\EventStore\Exception\DuplicatePlayheadException
     */
    public function it_throws_an_exception_when_appending_a_duplicate_playhead($id)
    {
        $domainMessage     = $this->createDomainMessage($id, 0);
        $baseStream        = new DomainEventStream([$domainMessage]);
        $this->eventStore->append($id, $baseStream);
        $appendedEventStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->append($id, $appendedEventStream);
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

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ]);

        $this->eventStore->append($id, $domainEventStream);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_loads_events_starting_from_a_given_playhead($id)
    {
        $dateTime          = DateTime::fromString('2014-03-12T14:17:19.176169+00:00');
        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
        ]);

        $this->eventStore->append($id, $domainEventStream);

        $expected = new DomainEventStream([
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
        ]);

        $this->assertEquals($expected, $this->eventStore->loadFromPlayhead($id, 2));
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_returns_empty_event_stream_when_no_events_are_committed_since_given_playhead($id)
    {
        $this->eventStore->append($id, new DomainEventStream([
            $this->createDomainMessage($id, 0),
        ]));

        $this->assertEquals(
            new DomainEventStream([]),
            $this->eventStore->loadFromPlayhead($id, 1)
        );
    }

    public function idDataProvider()
    {
        return [
            'Simple String' => [
                'Yolntbyaac', //You only live nine times because you are a cat
            ],
            'Identitiy' => [
                new StringIdentity(
                    'Yolntbyaac' //You only live nine times because you are a cat
                ),
            ],
            'Integer' => [
                42, // test an int
            ],
            'UUID String' => [
                (new Version4Generator())->generate(), // test UUID
            ],
        ];
    }

    protected function createDomainMessage($id, $playhead, $recordedOn = null)
    {
        return new DomainMessage($id, $playhead, new MetaData([]), new Event(), $recordedOn ? $recordedOn : DateTime::now());
    }
}

class Event implements Serializable
{
    public static function deserialize(array $data)
    {
        return new Event();
    }

    public function serialize()
    {
        return [];
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
