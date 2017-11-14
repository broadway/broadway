<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Auditing;

use PHPUnit\Framework\TestCase;

class CommandLoggerTest extends TestCase
{
    /**
     * @var TraceableLogger
     */
    private $logger;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var CommandLogger
     */
    private $commandAuditLogger;

    /**
     * @var CommandSerializer
     */
    private $commandSerializer;

    protected function setUp()
    {
        $this->logger = new TraceableLogger();

        $this->commandSerializer = $this->prophesize(CommandSerializer::class);

        $this->command = new Command();
        $this->exception = new MyException('Yolo', 5);

        $this->commandAuditLogger = new CommandLogger($this->logger, $this->commandSerializer->reveal());
    }

    /**
     * @test
     */
    public function it_logs_the_command_on_success()
    {
        $this->commandSerializer
            ->serialize($this->command)
            ->willReturn(['all' => 'the data']);

        $this->commandAuditLogger->onCommandHandlingSuccess($this->command);

        $this->assertCount(1, $this->logger->info);
        $this->assertEquals('{"status":"success","command":{"class":"Broadway\\\\Auditing\\\\Command","data":{"all":"the data"}}}', $this->logger->info[0]);
    }

    /**
     * @test
     */
    public function it_logs_the_command_on_failure()
    {
        $this->commandSerializer
            ->serialize($this->command)
            ->willReturn(['all' => 'the data']);

        $this->commandAuditLogger->onCommandHandlingFailure($this->command, $this->exception);

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

class TraceableLogger implements LoggerInterface
{
    public $info = [];

    public function emergency($message, array $context = [])
    {
    }

    public function alert($message, array $context = [])
    {
    }

    public function critical($message, array $context = [])
    {
    }

    public function error($message, array $context = [])
    {
    }

    public function warning($message, array $context = [])
    {
    }

    public function notice($message, array $context = [])
    {
    }

    public function info($message, array $context = [])
    {
        $this->info[] = $message;
    }

    public function debug($message, array $context = [])
    {
    }

    public function log($level, $message, array $context = [])
    {
    }
}

class Command
{
    public $name = 'name';
}

class MyException extends \Exception
{
}
