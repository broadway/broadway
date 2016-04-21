<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit_Framework_TestCase;

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
    private $streamType;
    private $aggregateId;

    /**
     * @param string $streamType
     */
    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandlerInterface $commandHandler,
        $streamType
    ) {
        $this->testCase       = $testCase;
        $this->eventStore     = $eventStore;
        $this->commandHandler = $commandHandler;
        $this->streamType     = $streamType;
        $this->aggregateId    = 1;
    }

    /**
     * @param string $aggregateId
     */
    public function withAggregateId($aggregateId)
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function given(array $events = null)
    {
        if ($events === null) {
            return $this;
        }

        $messages = array();
        $playhead = -1;
        foreach ($events as $event) {
            $playhead++;
            $messages[] = DomainMessage::recordNow($this->aggregateId, $playhead, new Metadata(array()), $event);
        }

        $this->eventStore->append($this->streamType, $this->aggregateId, new DomainEventStream($messages));

        return $this;
    }

    /**
     * @param mixed $command
     *
     * @return Scenario
     */
    public function when($command)
    {
        $this->eventStore->trace();

        $this->commandHandler->handle($command);

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function then(array $events)
    {
        $this->testCase->assertEquals($events, $this->eventStore->getEvents());

        $this->eventStore->clearEvents();

        return $this;
    }
}
