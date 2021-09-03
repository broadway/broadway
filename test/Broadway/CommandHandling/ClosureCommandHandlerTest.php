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

namespace MicroModule\Broadway\CommandHandling;

use MicroModule\Broadway\CommandHandling\Exception\ClosureParameterNotAnObjectException;
use MicroModule\Broadway\CommandHandling\Exception\CommandNotAnObjectException;
use PHPUnit\Framework\TestCase;

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

        $command = new ClosureCommandHandlerTestCommand();
        $commandHandler->handle($command);

        $this->assertTrue($command->handle);
    }

    /**
     * @test
     */
    public function it_throws_when_handling_a_non_object_command()
    {
        $commandHandler = new ClosureCommandHandler();

        $this->expectException(CommandNotAnObjectException::class);

        $commandHandler->handle('foo');
    }

    /**
     * @test
     */
    public function it_throws_when_adding_a_closure_without_an_object_argument()
    {
        $commandHandler = new ClosureCommandHandler();
        $this->expectException(ClosureParameterNotAnObjectException::class);

        $commandHandler->add(function ($params = null) { });
    }

    /**
     * @test
     */
    public function it_throws_when_adding_a_closure_without_an_object_argument_and_no_params()
    {
        $commandHandler = new ClosureCommandHandler();
        $this->expectException(ClosureParameterNotAnObjectException::class);

        $commandHandler->add(function () { });
    }
}

class ClosureCommandHandlerTestCommand
{
    public $handle = false;
}
