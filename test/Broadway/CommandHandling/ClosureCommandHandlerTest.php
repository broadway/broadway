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

use Broadway\CommandHandling\Exception\ClosureParameterNotAnObjectException;
use Broadway\CommandHandling\Exception\CommandNotAnObjectException;
use Broadway\TestCase;

class ClosureCommandHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_delegates_command_to_proper_handle_function()
    {
        $commandHandler = new ClosureCommandHandler();
        $commandHandler->add(function (ClosureCommandHandlerTestCommand $command) {
            $command->handle = true;
        });

        $command        = new ClosureCommandHandlerTestCommand();
        $commandHandler->handle($command);

        $this->assertTrue($command->handle);
    }

    /**
     * @test
     */
    public function it_throws_when_handling_a_non_object_command()
    {
        $commandHandler = new ClosureCommandHandler();
        $this->setExpectedException(CommandNotAnObjectException::class);
        $commandHandler->handle('foo');
    }

    /**
     * @test
     */
    public function it_throws_when_adding_a_closure_without_an_object_argument()
    {
        $commandHandler = new ClosureCommandHandler();
        $this->setExpectedException(ClosureParameterNotAnObjectException::class);
        $commandHandler->add(function($params = null) { });
    }
}

class ClosureCommandHandlerTestCommand
{
    public $handle = false;
}
