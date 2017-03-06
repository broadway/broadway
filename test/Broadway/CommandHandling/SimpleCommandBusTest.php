<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling;

use Broadway\TestCase;

class SimpleCommandBusTest extends TestCase
{
    private $commandBus;

    public function setUp()
    {
        $this->commandBus = new SimpleCommandBus();
    }

    /**
     * @test
     */
    public function it_dispatches_commands_to_subscribed_handlers()
    {
        $command = ['Hi' => 'There'];

        $this->commandBus->subscribe($this->createCommandHandlerMock($command));
        $this->commandBus->subscribe($this->createCommandHandlerMock($command));
        $this->commandBus->dispatch($command);
    }

    /**
     * @test
     */
    public function it_does_not_handle_new_commands_before_all_commandhandlers_have_run()
    {
        $command1 = ['foo' => 'bar'];
        $command2 = ['foo' => 'bas'];

        $commandHandler = $this->getMockBuilder('Broadway\CommandHandling\SimpleCommandHandler')->getMock();

        $commandHandler
            ->expects($this->at(0))
            ->method('handle')
            ->with($command1);

        $commandHandler
            ->expects($this->at(1))
            ->method('handle')
            ->with($command2);

        $this->commandBus->subscribe(new SimpleCommandBusTestHandler($this->commandBus, $command2));
        $this->commandBus->subscribe($commandHandler);
        $this->commandBus->dispatch($command1);
    }

    /**
     * @test
     */
    public function it_should_still_handle_commands_after_exception()
    {
        $command1 = ['foo' => 'bar'];
        $command2 = ['foo' => 'bas'];

        $commandHandler = $this->getMockBuilder('Broadway\CommandHandling\SimpleCommandHandler')->getMock();
        $simpleHandler  = $this->getMockBuilder('Broadway\CommandHandling\SimpleCommandHandler')->getMock();

        $commandHandler
            ->expects($this->at(0))
            ->method('handle')
            ->with($command1)
            ->will($this->throwException(new \Exception('I failed.')));

        $commandHandler
            ->expects($this->at(1))
            ->method('handle')
            ->with($command2);

        $simpleHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command2);

        $this->commandBus->subscribe($commandHandler);
        $this->commandBus->subscribe($simpleHandler);

        try {
            $this->commandBus->dispatch($command1);
        } catch (\Exception $e) {
            $this->assertEquals('I failed.', $e->getMessage());
        }

        $this->commandBus->dispatch($command2);
    }

    private function createCommandHandlerMock($expectedCommand)
    {
        $mock = $this->getMockBuilder('Broadway\CommandHandling\SimpleCommandHandler')->getMock();

        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($expectedCommand);

        return $mock;
    }
}

class SimpleCommandBusTestHandler implements CommandHandler
{
    private $commandBus;
    private $handled = false;
    private $dispatchableCommand;

    public function __construct($commandBus, $dispatchableCommand)
    {
        $this->commandBus          = $commandBus;
        $this->dispatchableCommand = $dispatchableCommand;
    }

    public function handle($command)
    {
        if (! $this->handled) {
            $this->commandBus->dispatch($this->dispatchableCommand);
            $this->handled = true;
        }
    }
}
