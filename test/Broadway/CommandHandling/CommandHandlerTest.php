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

use Broadway\CommandHandling\Exception\CommandNotAnObjectException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CommandHandlerTest extends TestCase
{
    #[Test]
    public function it_delegates_command_to_proper_handle_function(): void
    {
        $commandHandler = new TestCommandHandler();
        $command = new CommandHandlerTestCommand();
        $commandHandler->handle($command);

        $this->assertTrue($commandHandler->handled);
    }

    #[Test]
    #[DataProvider(methodName: 'unresolvableCommands')]
    public function handle_should_throw_exception_when_impossible_to_delegate_to_a_valid_method($command): void
    {
        $commandHandler = new TestCommandHandler();

        $this->expectException(CommandNotAnObjectException::class);

        $commandHandler->handle($command);
    }

    public static function unresolvableCommands(): array
    {
        return [
            [null],
            [false],
            ['foo'],
            [1],
            [['foo', 'bar']],
        ];
    }
}

final class TestCommandHandler extends SimpleCommandHandler
{
    private(set) bool $handled = false {
        get => $this->handled;
        set => $value;
    }

    public function handleCommandHandlerTestCommand(CommandHandlerTestCommand $command): void
    {
        $this->handled = true;
    }
}

final readonly class CommandHandlerTestCommand
{
}
