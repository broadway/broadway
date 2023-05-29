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

namespace Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit\Framework\TestCase;

/**
 * Helper testing scenario to test command handlers.
 *
 * The scenario will help with testing command handlers. A scenario consists of
 * three steps:
 *
 * 1) given(): Load a history of events in the event store
 * 2) when():  Dispatch a command
 * 3) then():  events that should have been persisted
 */
class Scenario
{
    private $eventStore;
    private $commandHandler;
    private $testCase;
    private $aggregateId;

    public function __construct(
        TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandler $commandHandler
    ) {
        $this->testCase = $testCase;
        $this->eventStore = $eventStore;
        $this->commandHandler = $commandHandler;
        $this->aggregateId = '1';
    }

    public function withAggregateId(string $aggregateId): self
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param mixed[] $events
     */
    public function given(?array $events): self
    {
        if (null === $events) {
            return $this;
        }

        $messages = [];
        $playhead = -1;
        foreach ($events as $event) {
            ++$playhead;
            $messages[] = DomainMessage::recordNow($this->aggregateId, $playhead, new Metadata([]), $event);
        }

        $this->eventStore->append($this->aggregateId, new DomainEventStream($messages));

        return $this;
    }

    public function when($command): self
    {
        $this->eventStore->trace();

        $this->commandHandler->handle($command);

        return $this;
    }

    /**
     * @param mixed[] $events
     */
    public function then(array $events): self
    {
        $this->testCase->assertEquals($events, $this->eventStore->getEvents());

        $this->eventStore->clearEvents();

        return $this;
    }
}
