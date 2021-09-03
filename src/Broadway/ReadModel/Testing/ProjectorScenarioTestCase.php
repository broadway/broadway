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

namespace MicroModule\Broadway\ReadModel\Testing;

use MicroModule\Broadway\ReadModel\InMemory\InMemoryRepository;
use MicroModule\Broadway\ReadModel\Projector;
use PHPUnit\Framework\TestCase;

/**
 * Base test case that can be used to set up a projector scenario.
 */
abstract class ProjectorScenarioTestCase extends TestCase
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
        $repository = new InMemoryRepository();

        return new Scenario($this, $repository, $this->createProjector($repository));
    }

    abstract protected function createProjector(InMemoryRepository $repository): Projector;
}
