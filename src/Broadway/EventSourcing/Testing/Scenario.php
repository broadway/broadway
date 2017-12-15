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

namespace Broadway\EventSourcing\Testing;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use PHPUnit\Framework\TestCase;

/**
 * Helper testing scenario to test command event sourced aggregate roots.
 *
 * The scenario will help with testing event sourced aggregate roots. A
 * scenario consists of three steps:
 *
 * 1) given(): Initialize the aggregate root using a history of events
 * 2) when():  A callable that calls a method on the event sourced aggregate root
 * 3) then():  Events that should have been applied
 */
class Scenario
{
    private $testCase;
    private $factory;

    private $aggregateRootClass;
    private $aggregateRootInstance;
    private $aggregateId;

    /**
     * @param TestCase         $testCase
     * @param AggregateFactory $factory
     * @param string           $aggregateRootClass
     */
    public function __construct(TestCase $testCase, AggregateFactory $factory, string $aggregateRootClass)
    {
        $this->testCase = $testCase;
        $this->factory = $factory;
        $this->aggregateRootClass = $aggregateRootClass;
        $this->aggregateId = '1';
    }

    /**
     * @param string $aggregateId
     *
     * @return Scenario
     */
    public function withAggregateId(string $aggregateId): self
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param array $givens
     *
     * @return Scenario
     */
    public function given(array $givens = null): self
    {
        if (null === $givens) {
            return $this;
        }

        $messages = [];
        $playhead = -1;
        foreach ($givens as $event) {
            ++$playhead;
            $messages[] = DomainMessage::recordNow(
                $this->aggregateId, $playhead, new Metadata([]), $event
            );
        }

        $this->aggregateRootInstance = $this->factory->create(
            $this->aggregateRootClass, new DomainEventStream($messages)
        );

        return $this;
    }

    /**
     * @param callable $when
     *
     * @return Scenario
     */
    public function when(callable $when): self
    {
        if (!is_callable($when)) {
            return $this;
        }

        if (null === $this->aggregateRootInstance) {
            $this->aggregateRootInstance = $when($this->aggregateRootInstance);

            $this->testCase->assertInstanceOf($this->aggregateRootClass, $this->aggregateRootInstance);
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
    public function then(array $thens): self
    {
        $this->testCase->assertEquals($thens, $this->getEvents());

        return $this;
    }

    /**
     * @return array Payloads of the recorded events
     */
    private function getEvents(): array
    {
        return array_map(function (DomainMessage $message) {
            return $message->getPayload();
        }, iterator_to_array($this->aggregateRootInstance->getUncommittedEvents()));
    }
}
