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

namespace Broadway\CommandHandling\Testing;

use PHPUnit\Framework\TestCase;

class TraceableCommandBusTest extends TestCase
{
    /**
     * @var TraceableCommandBus
     */
    private $commandBus;

    protected function setUp(): void
    {
        $this->commandBus = new TraceableCommandBus();
    }

    /**
     * @test
     */
    public function it_records_commands_when_recording_is_activated()
    {
        $command1 = ['Not' => 'Recorded'];
        $command2 = ['Hello' => 'There'];
        $command3 = ['Tomato' => 'Juice'];

        $this->commandBus->dispatch($command1);
        $this->assertEquals($this->commandBus->getRecordedCommands(), []);

        $this->commandBus->record();

        $this->commandBus->dispatch($command2);
        $this->commandBus->dispatch($command3);

        $this->assertEquals($this->commandBus->getRecordedCommands(), [$command2, $command3]);
    }
}
