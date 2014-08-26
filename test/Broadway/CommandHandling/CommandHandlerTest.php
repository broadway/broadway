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
    public function handle_should_delegate_command_to_proper_handle_function()
    {
        $commandHandler = new TestCommandHandler();
        $command        = new CommandHandlerTestCommand();
        $commandHandler->handle($command);

        $this->assertTrue($commandHandler->handled);
    }
}

class TestCommandHandler extends CommandHandler
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
