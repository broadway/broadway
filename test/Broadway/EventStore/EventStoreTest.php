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
use Broadway\Upcasting\SequentialUpcasterChain;
use Broadway\Upcasting\Upcaster;
use Rhumsaa\Uuid\Uuid;

abstract class EventStoreTest extends TestCase
{
    /**
     * @var EventStoreInterface
     */
    protected $eventStore;

    protected $upcasterChain;

    /**
     * @var TestUpcaster
     */
    private $upcaster;

    protected function setUp()
    {
        $this->upcaster = new TestUpcaster();
        $this->upcasterChain = new SequentialUpcasterChain(array($this->upcaster));
    }

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
     * @dataProvider idDataProvider
     * @expectedException Broadway\EventStore\EventStreamNotFoundException
     */
    public function it_throws_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate($id)
    {
        $this->eventStore->load($id);
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
        $this->eventStore->append($id, $baseStream);
        $appendedEventStream = new DomainEventStream(array($domainMessage));

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

        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ));

        $this->eventStore->append($id, $domainEventStream);
    }

    /**
     * @test
     * @dataProvider idDataProvider
     */
    public function it_upcasts_loaded_events($id)
    {
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ));

        $this->eventStore->append($id, $domainEventStream);

        $this->upcaster->setUpcastingCallback(function (array $serializedEvent) {
            $serializedEvent['payload']['isUpcasted'] = true;

            return $serializedEvent;
        });

        foreach($this->eventStore->load($id) as $event) {
            $this->assertTrue($event->getPayload()->isUpcasted());
        }
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
        return new DomainMessage($id, $playhead, new MetaData(array()), new Event(false), $recordedOn ? $recordedOn : DateTime::now());
    }
}

class Event implements SerializableInterface
{
    private $isUpcasted = false;

    public function __construct($isUpcasted)
    {
        $this->isUpcasted = $isUpcasted;
    }

    public static function deserialize(array $data)
    {
        return new Event($data['isUpcasted']);
    }

    public function serialize()
    {
        return array(
            'isUpcasted' => $this->isUpcasted
        );
    }

    public function isUpcasted()
    {
        return $this->isUpcasted;
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

final class TestUpcaster implements Upcaster
{
    private $callback;

    public function setUpcastingCallback($callback)
    {
        $this->callback = $callback;
    }

    public function supports(array $serializedEvent)
    {
        return $serializedEvent['class'] === 'Broadway\EventStore\Event';
    }

    public function upcast(array $serializedEvent)
    {
        if ($this->callback) {
            $serializedEvent = call_user_func($this->callback, $serializedEvent);
        }

        return $serializedEvent;
    }
}
