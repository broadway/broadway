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
     *
     * @expectedException \Broadway\CommandHandling\Exception\CommandNotAnObjectException
     */
    public function it_throws_when_handling_a_non_object_command()
    {
        $commandHandler = new ClosureCommandHandler();
        $commandHandler->handle('foo');
    }

    /**
     * @test
     *
     * @expectedException \Broadway\CommandHandling\Exception\ClosureParameterNotAnObjectException
     */
    public function it_throws_when_adding_a_closure_without_an_object_argument()
    {
        $commandHandler = new ClosureCommandHandler();
        $commandHandler->add(function($params = null) { });
    }
}

class ClosureCommandHandlerTestCommand
{
    public $handle = false;
}
