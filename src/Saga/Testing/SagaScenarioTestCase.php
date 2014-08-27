<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\Testing;

use Broadway\CommandHandling\Testing\TraceableCommandBus;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\Uuid\UuidGenerator;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaMetadataFactory;
use Broadway\Saga\MultipleSagaManager;
use Broadway\Saga\State\InMemoryRepository;
use Broadway\Saga\State\StateManager;
use PHPUnit_Framework_TestCase as TestCase;

class SagaScenarioTestCase extends TestCase
{
    protected function createScenario($sagaClass, UuidGenerator $uuidGenerator)
    {
        $traceableCommandBus = new TraceableCommandBus();
        $saga                = new $sagaClass($traceableCommandBus, $uuidGenerator);
        $sagaStateRepository = new InMemoryRepository();
        $sagaManager         = new MultipleSagaManager(
            $sagaStateRepository,
            array($saga),
            new StateManager($sagaStateRepository, $uuidGenerator),
            new StaticallyConfiguredSagaMetadataFactory(),
            new EventDispatcher()
        );

        return new Scenario($this, $sagaManager, $traceableCommandBus);
    }
}
