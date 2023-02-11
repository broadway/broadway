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

namespace Broadway\CommandHandling;

use Broadway\EventDispatcher\EventDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventDispatchingCommandBusTest extends TestCase
{
    /**
     * @var CommandBus|MockObject
     */
    private $baseCommandBus;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var EventDispatcher|MockObject
     */
    private $eventDispatcher;

    /**
     * @var EventDispatchingCommandBus
     */
    private $eventDispatchingCommandBus;

    /**
     * @var CommandHandler|MockObject
     */
    private $subscriber;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->baseCommandBus = $this->createMock(CommandBus::class);
        $this->subscriber = $this->createMock(CommandHandler::class);

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

        $this->expectException(MyException::class);

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

class MyException extends \Exception
{
}
