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

namespace Broadway\EventSourcing\AggregateFactory;

use Assert\Assertion as Assert;
use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;

/**
 * Creates aggregates by passing a DomainEventStream to the given public static method
 * which is itself responsible for returning an instance of itself.
 * E.g. (\Vendor\AggregateRoot::instantiateForReconstitution())->initializeState($domainEventStream);.
 */
final class NamedConstructorAggregateFactory implements AggregateFactory
{
    /**
     * @var string the name of the method to call on the Aggregate
     */
    private $staticConstructorMethod;

    public function __construct(string $staticConstructorMethod = 'instantiateForReconstitution')
    {
        $this->staticConstructorMethod = $staticConstructorMethod;
    }

    public function create(string $aggregateClass, DomainEventStream $domainEventStream): EventSourcedAggregateRoot
    {
        $methodCall = sprintf('%s::%s', $aggregateClass, $this->staticConstructorMethod);

        Assert::true(
            method_exists($aggregateClass, $this->staticConstructorMethod),
            sprintf('NamedConstructorAggregateFactory expected %s to exist', $methodCall)
        );

        $aggregate = call_user_func($methodCall);

        Assert::isInstanceOf($aggregate, $aggregateClass);

        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
