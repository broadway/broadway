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

namespace Broadway\ReadModel\Testing;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListener;
use Broadway\ReadModel\Repository;
use PHPUnit\Framework\TestCase;

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
        TestCase $testCase,
        Repository $repository,
        EventListener $projector
    ) {
        $this->testCase = $testCase;
        $this->repository = $repository;
        $this->projector = $projector;
        $this->playhead = -1;
        $this->aggregateId = '1';
        $this->dateTimeGenerator = function ($event) {
            return DateTime::now();
        };
    }

    public function withAggregateId(string $aggregateId): self
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    public function withDateTimeGenerator(callable $dateTimeGenerator): self
    {
        $this->dateTimeGenerator = $dateTimeGenerator;

        return $this;
    }

    public function given(array $events = []): self
    {
        foreach ($events as $given) {
            $this->projector->handle($this->createDomainMessageForEvent($given, null));
        }

        return $this;
    }

    public function when($event, DateTime $occurredOn = null): self
    {
        $this->projector->handle($this->createDomainMessageForEvent($event, $occurredOn));

        return $this;
    }

    public function then(array $expectedData): self
    {
        $this->testCase->assertEquals($expectedData, $this->repository->findAll());

        return $this;
    }

    /**
     * @param ?DateTime $occurredOn
     */
    private function createDomainMessageForEvent($event, ?DateTime $occurredOn): DomainMessage
    {
        ++$this->playhead;

        if (null === $occurredOn) {
            $dateTimeGenerator = $this->dateTimeGenerator;
            $occurredOn = $dateTimeGenerator($event);
        }

        return new DomainMessage($this->aggregateId, $this->playhead, new Metadata([]), $event, $occurredOn);
    }
}
