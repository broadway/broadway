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

use MicroModule\Broadway\Domain\DomainEventStream;
use MicroModule\Broadway\Domain\DomainMessage;
use MicroModule\Broadway\Domain\Metadata;
use MicroModule\Broadway\EventSourcing\AggregateFactory\AggregateFactory;
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

    public function __construct(TestCase $testCase, AggregateFactory $factory, string $aggregateRootClass)
    {
        $this->testCase = $testCase;
        $this->factory = $factory;
        $this->aggregateRootClass = $aggregateRootClass;
        $this->aggregateId = '1';
    }

    public function withAggregateId(string $aggregateId): self
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param mixed[] $givens
     */
    public function given(?array $givens): self
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
     * @param mixed[] $thens
     */
    public function then(array $thens): self
    {
        $this->testCase->assertEquals($thens, $this->getEvents());

        return $this;
    }

    /**
     * @return mixed[] Payloads of the recorded events
     */
    private function getEvents(): array
    {
        return array_map(function (DomainMessage $message) {
            return $message->getPayload();
        }, iterator_to_array($this->aggregateRootInstance->getUncommittedEvents()));
    }
}
