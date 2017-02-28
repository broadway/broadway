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

class EventDispatchingCommandBusTest extends TestCase
{
    private $baseCommandBus;
    private $command;
    private $eventDispatcher;
    private $eventDispatchingCommandBus;
    private $subscriber;

    public function setUp()
    {
        $this->eventDispatcher = $this->getMockBuilder('Broadway\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->baseCommandBus = $this->getMockBuilder('Broadway\CommandHandling\CommandBus')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = $this->getMockBuilder('Broadway\CommandHandling\CommandHandler')
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
     * @expectedException Broadway\CommandHandling\MyException
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
}

use Exception;

class MyException extends Exception
{
}
