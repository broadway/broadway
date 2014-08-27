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

use Broadway\CommandHandling\CommandHandler;
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

    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandler $commandHandler
    ) {
        $this->testCase       = $testCase;
        $this->eventStore     = $eventStore;
        $this->commandHandler = $commandHandler;
    }

    /**
     * @param array $events
     * @param string $id
     *
     * @return Scenario
     */
    public function given(array $events = null, $id = null)
    {
        if ($events === null) {
            return $this;
        }

        if ($id === null) {
            $id = 1;
        }

        $messages = array();
        $playhead = -1;
        foreach ($events as $event) {
            $playhead++;
            $messages[] = DomainMessage::recordNow($id, $playhead, new Metadata(array()), $event);
        }

        $this->eventStore->append($id, new DomainEventStream($messages));

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

        return $this;
    }
}
