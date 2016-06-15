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
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class CommandHandlerScenarioTestCase extends TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    public function setUp()
    {
        $this->scenario = $this->createScenario();
    }

    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        $eventStore     = new TraceableEventStore(new InMemoryEventStore());
        $eventBus       = new SimpleEventBus();
        $commandHandler = $this->createCommandHandler($eventStore, $eventBus);

        return new Scenario($this, $eventStore, $commandHandler, $this->getStreamType());
    }

    /**
     * @return string
     */
    abstract protected function getStreamType();

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface   $eventBus
     *
     * @return CommandHandlerInterface
     */
    abstract protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus);
}
