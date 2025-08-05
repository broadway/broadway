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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventDispatchingCommandBusTest extends TestCase
{
    /** @phpstan-var MockObject<CommandBus> */
    private CommandBus $baseCommandBus;

    private Command $command;

    /** @phpstan-var MockObject<EventDispatcher> */
    private EventDispatcher $eventDispatcher;

    private EventDispatchingCommandBus $eventDispatchingCommandBus;

    /** @phpstan-var MockObject<CommandHandler> */
    private CommandHandler $subscriber;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->baseCommandBus = $this->createMock(CommandBus::class);
        $this->subscriber = $this->createMock(CommandHandler::class);

        $this->command = new Command();

        $this->eventDispatchingCommandBus = new EventDispatchingCommandBus($this->baseCommandBus, $this->eventDispatcher);
    }

    #[Test]
    public function it_dispatches_the_success_event(): void
    {
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(EventDispatchingCommandBus::EVENT_COMMAND_SUCCESS, ['command' => $this->command]);

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }

    #[Test]
    public function it_dispatches_the_failure_event_and_forwards_the_exception(): void
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

    #[Test]
    public function it_forwards_the_dispatched_command(): void
    {
        $this->baseCommandBus->expects($this->once())
            ->method('dispatch')
            ->with($this->command);

        $this->eventDispatchingCommandBus->dispatch($this->command);
    }

    #[Test]
    public function it_forwards_the_subscriber(): void
    {
        $this->baseCommandBus->expects($this->once())
            ->method('subscribe')
            ->with($this->subscriber);

        $this->eventDispatchingCommandBus->subscribe($this->subscriber);
    }
}

final readonly class Command
{
}

final class MyException extends \Exception
{
}
