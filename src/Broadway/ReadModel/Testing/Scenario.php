<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\Testing;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListener;
use Broadway\ReadModel\Repository;
use PHPUnit_Framework_TestCase;

/**
 * Helper testing scenario to test projects.
 *
 * The scenario will help with testing projectors. A scenario consists of
 * three steps:
 *
 * 1) given(): Lets the projector handle some events
 * 2) when():  When a specific event is handled
 * 3) then():  The repository should contain these read models
 */
class Scenario
{
    private $testCase;
    private $projector;
    private $repository;
    private $playhead;
    private $aggregateId;
    private $dateTimeGenerator;

    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        Repository $repository,
        EventListener $projector
    ) {
        $this->testCase          = $testCase;
        $this->repository        = $repository;
        $this->projector         = $projector;
        $this->playhead          = -1;
        $this->aggregateId       = 1;
        $this->dateTimeGenerator = function($event) {
            return DateTime::now();
        };
    }

    /**
     * @param string $aggregateId
     *
     * @return Scenario
     */
    public function withAggregateId($aggregateId)
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @return Scenario
     */
    public function withDateTimeGenerator(callable $dateTimeGenerator)
    {
        $this->dateTimeGenerator = $dateTimeGenerator;

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function given(array $events = [])
    {
        foreach ($events as $given) {
            $this->projector->handle($this->createDomainMessageForEvent($given));
        }

        return $this;
    }

    /**
     * @param mixed $event
     *
     * @return Scenario
     */
    public function when($event, DateTime $occurredOn = null)
    {
        $this->projector->handle($this->createDomainMessageForEvent($event, $occurredOn));

        return $this;
    }

    /**
     * @param array $expectedData
     *
     * @return Scenario
     */
    public function then(array $expectedData)
    {
        $this->testCase->assertEquals($expectedData, $this->repository->findAll());

        return $this;
    }

    private function createDomainMessageForEvent($event, DateTime $occurredOn = null)
    {
        $this->playhead++;

        if (null === $occurredOn) {
            $dateTimeGenerator = $this->dateTimeGenerator;
            $occurredOn        = $dateTimeGenerator($event);
        }

        return new DomainMessage($this->aggregateId, $this->playhead, new Metadata([]), $event, $occurredOn);
    }
}
