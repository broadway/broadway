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

use Broadway\CommandHandling\Exception\CommandHandlingException;
use Broadway\TestCase;

/**
 * Class HandlingExceptionAwareCommandBusTest
 */
class HandlingExceptionAwareCommandBusTest extends TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|CommandBusInterface */
    private $commandBusMock;

    /**
     * @var HandlingExceptionAwareCommandBus
     */
    private $handlingCommandBus;

    protected function setUp()
    {
        $this->commandBusMock = $this
            ->getMockBuilder(CommandBusInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->handlingCommandBus = new HandlingExceptionAwareCommandBus(
            $this->commandBusMock
        );
    }

    /**
     * @test
     */
    public function it_forwards_correct_exception_when_using_handling_exception()
    {
        $command = array('foo' => 'bar');
        $originalException = new MyException();
        $exception = new CommandHandlingException($originalException, []);

        $this->commandBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->will($this->throwException($exception));

        $this->setExpectedException(MyException::class);
        $this->handlingCommandBus->dispatch($command);
    }

    /**
     * @test
     */
    public function it_forwards_correct_exception()
    {
        $command = array('foo' => 'bar');
        $exception = new MyException();

        $this->commandBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->will($this->throwException($exception));

        $this->setExpectedException(MyException::class);
        $this->handlingCommandBus->dispatch($command);
    }
}
