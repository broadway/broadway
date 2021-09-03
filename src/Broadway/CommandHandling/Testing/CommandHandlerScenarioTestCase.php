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

namespace MicroModule\Broadway\CommandHandling\Testing;

use MicroModule\Broadway\CommandHandling\CommandHandler;
use MicroModule\Broadway\EventHandling\EventBus;
use MicroModule\Broadway\EventHandling\SimpleEventBus;
use MicroModule\Broadway\EventStore\EventStore;
use MicroModule\Broadway\EventStore\InMemoryEventStore;
use MicroModule\Broadway\EventStore\TraceableEventStore;
use PHPUnit\Framework\TestCase;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class CommandHandlerScenarioTestCase extends TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    protected function setUp(): void
    {
        $this->scenario = $this->createScenario();
    }

    protected function createScenario(): Scenario
    {
        $eventStore = new TraceableEventStore(new InMemoryEventStore());
        $eventBus = new SimpleEventBus();
        $commandHandler = $this->createCommandHandler($eventStore, $eventBus);

        return new Scenario($this, $eventStore, $commandHandler);
    }

    /**
     * Create a command handler for the given scenario test case.
     */
    abstract protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler;
}
