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

namespace MicroModule\Broadway\EventSourcing\Testing;

use MicroModule\Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use MicroModule\Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use PHPUnit\Framework\TestCase;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class AggregateRootScenarioTestCase extends TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    protected function setUp(): void
    {
        $this->scenario = $this->createScenario();
    }

    protected function createScenario(): Scenario
    {
        $aggregateRootClass = $this->getAggregateRootClass();
        $factory = $this->getAggregateRootFactory();

        return new Scenario($this, $factory, $aggregateRootClass);
    }

    /**
     * Returns a string representing the aggregate root.
     *
     * @return string AggregateRoot
     */
    abstract protected function getAggregateRootClass(): string;

    /**
     * Returns a factory for instantiating an aggregate.
     *
     * @return AggregateFactory $factory
     */
    protected function getAggregateRootFactory(): AggregateFactory
    {
        return new PublicConstructorAggregateFactory();
    }
}
