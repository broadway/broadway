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

/**
 * Test file for CommandBusCommandAwareAdapter
 */
class CommandBusCommandAwareAdapterTest extends TestCase
{
    /**
     * @var CommandBusCommandAwareAdapter
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CommandBusInterface
     */
    private $adaptee;

    /**
     * @var\PHPUnit_Framework_MockObject_MockObject|CommandHandlerInterface
     */
    private $commandHandler;

    public function setUp()
    {
        $this->adaptee = $this->getMock('Broadway\CommandHandling\CommandBusInterface');
        $this->commandHandler = $this->getMock('Broadway\CommandHandling\CommandHandlerInterface');
        $this->adapter = new CommandBusCommandAwareAdapter($this->adaptee);
    }

    /**
     * @test
     */
    public function it_should_dispatch_valid_commands_to_the_actual_command_bus()
    {
        $command = new KissMyWifeCommand();
        $this->adaptee->expects($this->once())->method('dispatch')
            ->with($command);

        $this->adapter->dispatch($command);
    }

    /**
     * @test
     */
    public function it_does_not_allow_objects_which_are_not_implementing_the_interface()
    {
        $command = new IAmNoTACommand();
        $exception = false;
        try {
            $this->adapter->dispatch($command);
        } catch (\Exception $e) {
            $exception = true;
        }
        $this->assertTrue($exception);
    }

    /**
     * @test
     */
    public function it_passes_the_subscribe_method_to_the_adaptee()
    {
        $this->adaptee->expects($this->once())->method('subscribe')->with($this->commandHandler);

        $this->adapter->subscribe($this->commandHandler);
    }
}

/**
 * An invalid command
 */
class IAmNoTACommand {}

/**
 * A command that implements the interface
 */
class KissMyWifeCommand implements CommandInterface {}
