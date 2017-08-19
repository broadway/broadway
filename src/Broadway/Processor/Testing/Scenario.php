<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * @author Francesco Trucchia <francesco@trucchia.it>
 */

namespace Broadway\Processor\Testing;

use Broadway\CommandHandling\Testing\TraceableCommandBus;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Broadway\Domain\Metadata;

/**
 * Helper testing scenario to test processor.
 *
 * The scenario will help with testing processor. A scenario consists of
 * three steps:
 *
 * 1) when():  Dispatch a command
 * 2) then():  commands that should have been persisted
 */
class Scenario
{
    protected $processor;
    protected $playhead;
    protected $traceableCommandBus;
    protected $testCase;

    /**
     * Scenario constructor.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param TraceableCommandBus         $traceableCommandBus
     * @param EventListener      $processor
     */
    public function __construct(
        \PHPUnit_Framework_TestCase $testCase,
        TraceableCommandBus $traceableCommandBus,
        EventListener $processor
    ) {
        $this->testCase = $testCase;
        $this->traceableCommandBus = $traceableCommandBus;
        $this->processor = $processor;
    }

    /**
     * @param mixed $event
     *
     * @return Scenario
     */
    public function when($event)
    {
        $this->traceableCommandBus->record();

        $this->processor->handle($this->createDomainMessageForEvent($event));

        return $this;
    }

    /**
     * @param array $commands
     *
     * @return Scenario
     */
    public function then(array $commands)
    {
        $this->testCase->assertEquals($commands, $this->traceableCommandBus->getRecordedCommands());

        return $this;
    }

    /**
     * @param $event
     *
     * @return DomainMessage
     */
    private function createDomainMessageForEvent($event)
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), $event);
    }
}
