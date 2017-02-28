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

class CommandHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_delegates_command_to_proper_handle_function()
    {
        $commandHandler = new TestCommandHandler();
        $command        = new CommandHandlerTestCommand();
        $commandHandler->handle($command);

        $this->assertTrue($commandHandler->handled);
    }

    /**
     * @test
     *
     * @dataProvider unresolvableCommands
     */
    public function handle_should_throw_exception_when_impossible_to_delegate_to_a_valid_method($command)
    {
        $commandHandler = new TestCommandHandler();
        $this->setExpectedException('Broadway\CommandHandling\Exception\CommandNotAnObjectException');
        $commandHandler->handle($command);
    }

    public function unresolvableCommands()
    {
        return [
            [null],
            [false],
            ['foo'],
            [1],
            [['foo', 'bar']]
        ];
    }
}

class TestCommandHandler extends SimpleCommandHandler
{
    public $handled = false;

    public function handleCommandHandlerTestCommand(CommandHandlerTestCommand $command)
    {
        $this->handled = true;
    }
}

class CommandHandlerTestCommand
{
}
