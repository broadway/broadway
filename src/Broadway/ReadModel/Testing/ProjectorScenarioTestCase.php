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

namespace Broadway\ReadModel\Testing;

use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
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

    public function setUp()
    {
        $this->scenario = $this->createScenario();
    }

    /**
     * @return Scenario
     */
    protected function createScenario(): Scenario
    {
        $repository = new InMemoryRepository();

        return new Scenario($this, $repository, $this->createProjector($repository));
    }

    /**
     * @return Projector
     */
    abstract protected function createProjector(InMemoryRepository $repository): Projector;
}
