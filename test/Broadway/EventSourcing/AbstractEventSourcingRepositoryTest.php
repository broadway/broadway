<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventSourcing;

use Assert\InvalidArgumentException;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnricher;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnrichingEventStreamDecorator;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\ReadModel\Projector;
use Broadway\Repository\AggregateNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

abstract class AbstractEventSourcingRepositoryTest extends TestCase
{
    protected TraceableEventBus $eventBus;

    protected TraceableEventStoreDecorator $eventStreamDecorator;

    protected EventStore $eventStore;

    protected EventSourcingRepository $repository;

    protected function setUp(): void
    {
        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());
        $this->eventStore->trace();

        $this->eventBus = new TraceableEventBus(new SimpleEventBus());
        $this->eventBus->trace();

        $this->eventStreamDecorator = new TraceableEventStoreDecorator();
        $this->eventStreamDecorator->trace();

        $this->repository = $this->createEventSourcingRepository($this->eventStore, $this->eventBus, [$this->eventStreamDecorator]);
    }

    #[Test]
    #[DataProvider(methodName: 'objectsNotOfConfiguredClass')]
    public function it_throws_an_exception_when_adding_an_aggregate_that_is_not_of_the_configured_class($aggregate): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->repository->save($aggregate);
    }

    public static function objectsNotOfConfiguredClass(): array
    {
        return [
            [new TestAggregate()],
            [new AnotherTestEventSourcedAggregate()],
        ];
    }

    #[Test]
    public function it_adds_an_aggregate_root(): void
    {
        $aggregate = $this->createAggregate();
        $aggregate->apply(new DidNumberEvent(42));
        $aggregate->apply(new DidNumberEvent(1337));

        $this->repository->save($aggregate);

        $expected = [new DidNumberEvent(42), new DidNumberEvent(1337)];
        $this->assertEquals($expected, $this->eventStore->getEvents());
        $this->assertEquals($expected, $this->eventBus->getEvents());
    }

    #[Test]
    public function it_loads_an_aggregate(): void
    {
        $this->eventStore->append(42, new DomainEventStream([
            DomainMessage::recordNow(42, 0, new Metadata([]), new DidNumberEvent(1337)),
        ]));

        $aggregate = $this->repository->load(42);

        $expectedAggregate = $this->createAggregate();
        $expectedAggregate->apply(new DidNumberEvent(1337));
        $expectedAggregate->getUncommittedEvents();

        $this->assertEquals($expectedAggregate, $aggregate);
    }

    #[Test]
    public function it_throws_an_exception_if_aggregate_was_not_found(): void
    {
        $this->expectException(AggregateNotFoundException::class);

        $this->repository->load('does-not-exist');
    }

    #[Test]
    public function it_calls_the_event_stream_decorators(): void
    {
        $aggregate = $this->createAggregate();
        $aggregate->apply(new DidNumberEvent(42));

        $this->repository->save($aggregate);

        $this->assertTrue($this->eventStreamDecorator->isCalled());
    }

    #[Test]
    public function it_calls_the_event_stream_decorators_with_the_correct_arguments(): void
    {
        $event = new DidNumberEvent(42);

        $aggregate = $this->createAggregate();
        $aggregate->apply($event);

        $this->repository->save($aggregate);

        $lastCall = $this->eventStreamDecorator->getLastCall();

        $this->assertEquals($aggregate->getAggregateRootId(), $lastCall['aggregateIdentifier']);
        $this->assertEquals(get_class($aggregate), $lastCall['aggregateType']);

        $events = iterator_to_array($lastCall['eventStream']);
        $this->assertCount(1, $events);

        $this->assertSame($event, $events[0]->getPayload());
    }

    #[Test]
    public function it_publishes_decorated_events(): void
    {
        $projector = new TestMetadataPublishedProjector();
        $this->eventBus->subscribe($projector);

        $repository = new EventSourcingRepository(
            $this->eventStore,
            $this->eventBus,
            get_class($this->createAggregate()),
            new PublicConstructorAggregateFactory(),
            [new MetadataEnrichingEventStreamDecorator([new TestDecorationMetadataEnricher()])]
        );

        $aggregate = $this->createAggregate();
        $aggregate->apply(new DidNumberEvent(42));
        $repository->save($aggregate);

        $metadata = $projector->metadata;
        $data = $metadata->serialize();

        $this->assertArrayHasKey('decoration_test', $data);
        $this->assertEquals('I am a decorated test', $data['decoration_test']);
    }

    abstract protected function createEventSourcingRepository(TraceableEventStore $eventStore, TraceableEventBus $eventBus, array $eventStreamDecorators): EventSourcingRepository;

    abstract protected function createAggregate(): EventSourcedAggregateRoot;
}

final readonly class DidNumberEvent
{
    public function __construct(public int $number)
    {
    }
}

final class AnotherTestEventSourcedAggregate extends EventSourcedAggregateRoot
{
    public function getAggregateRootId(): string
    {
        return '1337';
    }
}

final readonly class TestAggregate implements AggregateRoot
{
    public function getAggregateRootId(): string
    {
        return '42';
    }

    public function getUncommittedEvents(): DomainEventStream
    {
        return new DomainEventStream([]);
    }
}

final class TraceableEventstoreDecorator implements EventStreamDecorator
{
    private(set) bool $tracing = false {
        get => $this->tracing;
        set  => $value;
    }
    private(set) array $calls;

    public function decorateForWrite(string $aggregateType, string $aggregateIdentifier, DomainEventStream $eventStream): DomainEventStream
    {
        if ($this->tracing) {
            $this->calls[] = ['aggregateType' => $aggregateType, 'aggregateIdentifier' => $aggregateIdentifier, 'eventStream' => $eventStream];
        }

        return $eventStream;
    }

    public function trace(): void
    {
        $this->tracing = true;
    }

    public function isCalled(): bool
    {
        return count($this->calls) > 0;
    }

    public function getLastCall(): array
    {
        if (!$this->isCalled()) {
            throw new \RuntimeException('was never called');
        }

        return $this->calls[count($this->calls) - 1];
    }
}

class TestDecorationMetadataEnricher implements MetadataEnricher
{
    public function enrich(Metadata $metadata): Metadata
    {
        return new Metadata(['decoration_test' => 'I am a decorated test']);
    }
}

final class TestMetadataPublishedProjector extends Projector
{
    private(set) Metadata $metadata {
        get => $this->metadata;
        set  => $value;
    }

    public function applyDidNumberEvent(DidNumberEvent $event, DomainMessage $domainMessage): void
    {
        $this->metadata = $domainMessage->getMetadata();
    }
}
