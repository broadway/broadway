<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Assert\Assertion as Assert;
use Broadway\Domain\DomainEventStream;

/**
 * Creates aggregates by passing a DomainEventStream to the given public static method
 * which is itself responsible for returning an instance of itself.
 * E.g. (\Vendor\AggregateRoot::instantiateForReconstitution())->initializeState($domainEventStream);
 */
class NamedConstructorAggregateFactory implements AggregateFactory
{
    /**
     * @var string the name of the method to call on the Aggregate
     */
    private $staticConstructorMethod;

    public function __construct($staticConstructorMethod = 'instantiateForReconstitution')
    {
        $this->staticConstructorMethod = $staticConstructorMethod;
    }

    /**
     * {@inheritDoc}
     */
    public function create($aggregateClass, DomainEventStream $domainEventStream)
    {
        $methodCall = sprintf('%s::%s', $aggregateClass, $this->staticConstructorMethod);

        Assert::true(
            method_exists($aggregateClass, $this->staticConstructorMethod),
            sprintf('NamedConstructorAggregateFactory expected %s to exist', $methodCall)
        );

        $aggregate  = call_user_func($methodCall);

        Assert::isInstanceOf($aggregate, $aggregateClass);

        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
