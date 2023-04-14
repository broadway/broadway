<?php

declare(strict_types=1);

namespace Broadway\Upcasting;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\InMemoryEventStore;
use PHPUnit\Framework\TestCase;

class UpcastingEventStoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_call_upcaster_when_event_stream_is_not_empty(): void
    {
        $upcasterChain = $this->createMock(UpcasterChain::class);

        $eventStore = new UpcastingEventStore(new InMemoryEventStore(), $upcasterChain);

        $events[] = DomainMessage::recordNow(5, 0, new Metadata([]), 'Foo');
        $events[] = DomainMessage::recordNow(5, 1, new Metadata([]), 'Bar');

        $upcasterChain->expects($this->exactly(2))
            ->method('upcast')
            ->willReturnMap([
                [$events[0], $events[0]],
                [$events[1], $events[1]],
            ]);

        $eventStore->append(1, new DomainEventStream($events));
        $eventStore->load(1);
    }
}
