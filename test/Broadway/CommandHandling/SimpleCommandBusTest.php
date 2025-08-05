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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SimpleCommandBusTest extends TestCase
{
    private SimpleCommandBus $commandBus;

    protected function setUp(): void
    {
        $this->commandBus = new SimpleCommandBus();
    }

    #[Test]
    public function it_dispatches_commands_to_subscribed_handlers(): void
    {
        $command = ['Hi' => 'There'];

        $this->commandBus->subscribe($this->createCommandHandlerMock($command));
        $this->commandBus->subscribe($this->createCommandHandlerMock($command));
        $this->commandBus->dispatch($command);
    }

    #[Test]
    public function it_does_not_handle_new_commands_before_all_commandhandlers_have_run(): void
    {
        $command1 = ['foo' => 'bar'];
        $command2 = ['foo' => 'bas'];

        $commandHandler = $this->createMock(CommandHandler::class);

        $matcher = $this->exactly(2);
        $commandHandler
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                fn (array $data) => match ($matcher->numberOfInvocations()) {
                    1 => $data === $command1,
                    2 => $data === $command2,
                    default => throw new \Exception('Unexpected data'),
                }
            );

        $this->commandBus->subscribe(
            new class($this->commandBus, $command2) implements CommandHandler
            {
                private(set) bool $handled = false {
                    get => $this->handled;
                    set => $value;
                }

                public function __construct(
                    public readonly CommandBus $commandBus,
                    public readonly array $dispatchableCommand
                ) {
                }

                public function handle($command): void
                {
                    if (!$this->handled) {
                        $this->commandBus->dispatch($this->dispatchableCommand);
                        $this->handled = true;
                    }
                }
            }
        );
        $this->commandBus->subscribe($commandHandler);
        $this->commandBus->dispatch($command1);
    }

    #[Test]
    public function it_should_still_handle_commands_after_exception(): void
    {
        $command1 = ['foo' => 'bar'];
        $command2 = ['foo' => 'bas'];

        $commandHandler = $this->createMock(CommandHandler::class);
        $simpleHandler = $this->createMock(CommandHandler::class);

        $matcher = $this->exactly(2);
        $commandHandler
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                function ($command) use ($matcher, $command1, $command2) {
                    $this->assertTrue(
                        match (true) {
                            $matcher->numberOfInvocations() === 1 => $command === $command1,
                            $matcher->numberOfInvocations() === 2 => $command === $command2,
                        }
                    );

                    if ($matcher->numberOfInvocations() === 1) {
                        throw new \Exception('I failed.');
                    }
                }
            );

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

    /**
     * @phpstan-return MockObject<CommandHandler>
     */
    private function createCommandHandlerMock(array $expectedCommand): CommandHandler
    {
        $mock = $this->createMock(CommandHandler::class);

        $mock
            ->expects($this->once())
            ->method('handle')
            ->with($expectedCommand);

        return $mock;
    }
}
