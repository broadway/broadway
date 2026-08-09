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

namespace Broadway\Auditing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

class CommandLoggerTest extends TestCase
{
    private TraceableLogger $logger;

    private Command $command;

    private CommandLogger $commandAuditLogger;

    /** @phpstan-var MockBuilder<CommandSerializer> */
    private CommandSerializer $commandSerializer;

    protected function setUp(): void
    {
        $this->logger = new TraceableLogger();

        $this->commandSerializer = $this->createMock(CommandSerializer::class);

        $this->command = new Command();

        $this->commandAuditLogger = new CommandLogger($this->logger, $this->commandSerializer);
    }

    #[Test]
    public function it_logs_the_command_on_success(): void
    {
        $this->commandSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->command)
            ->willReturn(['all' => 'the data']);

        $this->commandAuditLogger->onCommandHandlingSuccess($this->command);

        $this->assertCount(1, $this->logger->info);
        $this->assertEquals('{"status":"success","command":{"class":"Broadway\\\\Auditing\\\\Command","data":{"all":"the data"}}}', $this->logger->info[0]);
    }

    #[Test]
    public function it_logs_the_command_on_failure(): void
    {
        $this->commandSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->command)
            ->willReturn(['all' => 'the data']);

        $this->commandAuditLogger->onCommandHandlingFailure($this->command, new MyException('Yolo', 5));

        $this->assertCount(1, $this->logger->info);
        $loggedData = json_decode($this->logger->info[0], true);

        $this->assertArrayHasKey('status', $loggedData);
        $this->assertEquals('failure', $loggedData['status']);
        $this->assertArrayHasKey('command', $loggedData);
        $this->assertArrayHasKey('class', $loggedData['command']);
        $this->assertEquals('Broadway\Auditing\Command', $loggedData['command']['class']);
        $this->assertArrayHasKey('data', $loggedData['command']);
        $this->assertEquals(['all' => 'the data'], $loggedData['command']['data']);

        $this->assertArrayHasKey('exception', $loggedData);
        $this->assertArrayHasKey('message', $loggedData['exception']);
        $this->assertArrayHasKey('file', $loggedData['exception']);
        $this->assertArrayHasKey('class', $loggedData['exception']);
        $this->assertArrayHasKey('line', $loggedData['exception']);
        $this->assertArrayHasKey('code', $loggedData['exception']);

        $this->assertEquals('Yolo', $loggedData['exception']['message']);
        $this->assertEquals('Broadway\Auditing\MyException', $loggedData['exception']['class']);
        $this->assertStringEndsWith('test/Broadway/Auditing/CommandLoggerTest.php', $loggedData['exception']['file']);
    }
}

use Psr\Log\LoggerInterface;

final class TraceableLogger implements LoggerInterface
{
    public array $info = [];

    public function emergency($message, array $context = []): void
    {
    }

    public function alert($message, array $context = []): void
    {
    }

    public function critical($message, array $context = []): void
    {
    }

    public function error($message, array $context = []): void
    {
    }

    public function warning($message, array $context = []): void
    {
    }

    public function notice($message, array $context = []): void
    {
    }

    public function info($message, array $context = []): void
    {
        $this->info[] = $message;
    }

    public function debug($message, array $context = []): void
    {
    }

    public function log($level, $message, array $context = []): void
    {
    }
}

final class Command
{
    public string $name = 'name';
}

final class MyException extends \Exception
{
}
