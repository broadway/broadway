<?php

namespace Broadway\EventSourcing\DomainMessageTypeEnrichment;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class DomainMessageTypeEventStreamDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_original_eventStream_if_events_do_not_have_type_accessor()
    {
        $decorator = new DomainMessageTypeEventStreamDecorator();
        $eventStream = $this->createDomainEventStreamWithoutTypeAccessor();

        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);
        $this->assertEquals($eventStream, $newEventStream);
    }

    /**
     * @test
     */
    public function it_returns_a_domain_eventstream_with_updated_domain_message_type_according_event_type()
    {
        $decorator = new DomainMessageTypeEventStreamDecorator();
        $eventStream = $this->createDomainEventStreamWithTypeAccessor();

        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);

        $messages = iterator_to_array($newEventStream);

        $this->assertCount(2, $messages);
        $expectedType = 'some.type';

        foreach ($messages as $message) {
            $this->assertEquals($expectedType, $message->getType());
        }
    }
    
    private function createDomainEventStreamWithoutTypeAccessor()
    {
        $payload = new AggregateEventWithoutAccessor();
        $m1 = DomainMessage::recordNow('id', 42, Metadata::kv('bar', 1337), $payload);

        return new DomainEventStream(array($m1));
    }

    private function createDomainEventStreamWithTypeAccessor()
    {
        $payload = new AggregateEventWithAccessor();
        $m1 = DomainMessage::recordNow('id', 42, new Metadata(), $payload);
        $m2 = DomainMessage::recordNow('id', 43, new Metadata(), $payload);

        return new DomainEventStream(array($m1, $m2));
    }

}

class AggregateEventWithoutAccessor
{

}

class AggregateEventWithAccessor
{
    public function getType()
    {
        return 'some.type';
    }
}
