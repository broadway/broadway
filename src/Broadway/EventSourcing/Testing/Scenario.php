<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing\Testing;

use PHPUnit_Framework_TestCase;

/**
 * Helper testing scenario to test command event sourced aggregate roots.
 *
 * The scenario will help with testing event sourced aggregate roots. A
 * scenario consists of three steps:
 *
 * 1) given(): Load a history of events in the event store
 * 2) when():  A callable that calls a method on the event sourced aggregate root
 * 3) then():  Events that should have been applied
 */
class Scenario
{
    private $testCase;
    private $aggregateRoot;
    private $aggregateRootInstance;

    /**
     * @param PHPUnit_Framework_TestCase $testcase
     * @param string                     $aggregateRoot
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase, $aggregateRoot)
    {
        $this->testCase      = $testCase;
        $this->aggregateRoot = $aggregateRoot;
    }

    /**
     * @param array $givens
     *
     * @return Scenario
     */
    public function given(array $givens = null)
    {
        if ($givens === null) {
            return $this;
        }

        $this->aggregateRootInstance = new $this->aggregateRoot();
        $this->aggregateRootInstance->initializeState($givens);

        return $this;
    }

    /**
     * @param callable $when
     *
     * @return Scenario
     */
    public function when(/* callable */ $when)
    {
        if (! is_callable($when)) {
            return $this;
        }

        if ($this->aggregateRootInstance === null) {
            $this->aggregateRootInstance = $when($this->aggregateRootInstance);

            $this->testCase->assertInstanceOf($this->aggregateRoot, $this->aggregateRootInstance);
        } else {
            $when($this->aggregateRootInstance);
        }

        return $this;
    }

    /**
     * @param array $thens
     *
     * @return Scenario
     */
    public function then(array $thens)
    {
        $this->testCase->assertEquals($thens, $this->aggregateRootInstance->getUncommittedEvents());

        return $this;
    }
}
