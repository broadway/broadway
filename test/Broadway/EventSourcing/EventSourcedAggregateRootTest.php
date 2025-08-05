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

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventSourcedAggregateRootTest extends TestCase
{
    #[Test]
    public function it_applies_using_an_incrementing_playhead(): void
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->apply(new AggregateEvent());
        $aggregateRoot->apply(new AggregateEvent());
        $eventStream = $aggregateRoot->getUncommittedEvents();

        $i = 0;
        foreach ($eventStream as $domainMessage) {
            $this->assertEquals($i, $domainMessage->getPlayhead());
            ++$i;
        }
        $this->assertEquals(2, $i);
    }

    #[Test]
    public function it_sets_internal_playhead_when_initializing(): void
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->initializeState($this->toDomainEventStream([new AggregateEvent()]));

        $aggregateRoot->apply(new AggregateEvent());

        $eventStream = $aggregateRoot->getUncommittedEvents();
        foreach ($eventStream as $domainMessage) {
            $this->assertEquals(1, $domainMessage->getPlayhead());
        }
    }

    #[Test]
    public function it_calls_apply_for_specific_events(): void
    {
        $aggregateRoot = new MyTestAggregateRoot();
        $aggregateRoot->initializeState($this->toDomainEventStream([new AggregateEvent()]));

        $this->assertTrue($aggregateRoot->isCalled);
    }

    private function toDomainEventStream(array $events): DomainEventStream
    {
        $messages = [];
        $playhead = -1;
        foreach ($events as $event) {
            ++$playhead;
            $messages[] = DomainMessage::recordNow(1, $playhead, new Metadata([]), $event);
        }

        return new DomainEventStream($messages);
    }
}

class MyTestAggregateRoot extends EventSourcedAggregateRoot
{
    private(set) bool $isCalled = false {
        get => $this->isCalled;
        set  => $value;
    }

    public function getAggregateRootId(): string
    {
        return 'y0l0';
    }

    public function applyAggregateEvent($event): void
    {
        $this->isCalled = true;
    }
}

final readonly class AggregateEvent
{
}
