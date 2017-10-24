<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * @author Francesco Trucchia <francesco@trucchia.it>
 */

namespace Broadway\Processor\Testing;

use Broadway\CommandHandling\CommandBus;
use Broadway\CommandHandling\Testing\TraceableCommandBus;
use Broadway\Processor\Processor;

class ScenarioTest extends ProcessorScenarioTestCase
{
    /**
     * @test
     */
    public function it_trace_commands_dispatched()
    {
        $this->scenario
            ->when(new TestEvent())
            ->then([
                new TestCommand(),
                new TestCommand()
            ]);
    }

    /**
     * Create a processor for the given scenario test case.
     *
     * @param TraceableCommandBus $traceableCommandBus
     *
     * @return mixed
     */
    protected function createProcessor(TraceableCommandBus $traceableCommandBus)
    {
        return new TestProcessor($traceableCommandBus);
    }
}

class TestProcessor extends Processor
{
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    protected function handleTestEvent(TestEvent $event)
    {
        $this->commandBus->dispatch(new TestCommand());
        $this->commandBus->dispatch(new TestCommand());
    }
}

class TestEvent
{
}

class TestCommand
{
}
