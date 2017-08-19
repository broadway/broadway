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
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Base test case that can be used to set up a processor scenario.
 */
abstract class ProcessorScenarioTestCase extends TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    public function setUp()
    {
        $this->scenario = $this->createScenario();
    }

    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        $traceableCommandBus = new TraceableCommandBus();
        $process = $this->createProcessor($traceableCommandBus);

        return new Scenario($this, $traceableCommandBus, $process);
    }

    /**
     * Create a processor for the given scenario test case.
     *
     * @param TraceableCommandBus $traceableCommandBus
     *
     * @return mixed
     */
    abstract protected function createProcessor(TraceableCommandBus $traceableCommandBus);
}
