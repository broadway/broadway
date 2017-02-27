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

use Broadway\CommandHandling\Exception\CommandHandlingException;
use Broadway\EventDispatcher\EventDispatcherInterface;
use Broadway\TestCase;

class EventDispatchingCommandBusTest extends TestCase
{
    private $baseCommandBus;
    private $command;
    private $eventDispatcher;
    private $eventDispatchingCommandBus;
    private $subscriber;

    public function setUp()
    {
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->baseCommandBus = $this->getMockBuilder(CommandBusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = $this->getMockBuilder(CommandHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new Command();

        $this->eventDispatchingCommandBus = new EventDispatchingCommandBus($this->baseCommandBus, $this->eventDispatcher);
    }

    /**
     * @test
     */
    public function it_dispatches_the_success_event()
    {
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(EventDispatchingCommandBus::EVENT_COMMAND_SUCCESS, ['command' => $this->command]);

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }

    /**
     * @test
     * @expectedException \Broadway\CommandHandling\MyException
     */
    public function it_dispatches_the_failure_event_and_forwards_the_exception()
    {
        $exception = new MyException();
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                EventDispatchingCommandBus::EVENT_COMMAND_FAILURE,
                ['command' => $this->command, 'exception' => $exception]
            );

        $this->baseCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->command)
            ->will($this->throwException($exception));

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }

    /**
     * @test
     * @expectedException \Broadway\CommandHandling\MyException
     */
    public function it_specially_handles_command_handling_exception()
    {
        $exception = new MyException();
        $incompleteCommands =  array(new Command('foo'));
        $handlingException = new CommandHandlingException($exception, $incompleteCommands);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                EventDispatchingCommandBus::EVENT_COMMAND_FAILURE,
                array('command' => $this->command, 'exception' => $exception, 'incomplete_commands' => $incompleteCommands)
            );

        $this->baseCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->command)
            ->will($this->throwException($handlingException));

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }


    /**
     * @test
     */
    public function it_forwards_the_dispatched_command()
    {
        $this->baseCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->command);

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }

    /**
     * @test
     */
    public function it_forwards_the_subscriber()
    {
        $this->baseCommandBus->expects($this->once())
            ->method('subscribe')
            ->with($this->subscriber);

        $this->eventDispatchingCommandBus->subscribe($this->subscriber);
    }
}

class Command
{
    private $value;
    public function __construct($value = null)
    {
        $this->value = $value;
    }
}

use Exception;

class MyException extends Exception
{
}
