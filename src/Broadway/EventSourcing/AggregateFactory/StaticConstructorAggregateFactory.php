<?php namespace Broadway\EventSourcing\AggregateFactory;

use Assert\Assertion as Assert;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventSourcing\EventSourcedAggregateRoot;

/**
 * Creates aggregates by passing a DomainEventStream to the given public static method
 * which is itself responsible for returning an instance of itself.
 * E.g. (\Vendor\AggregateRoot::instantiateForReconstitution())->initializeState($domainEventStream);
 */
class StaticConstructorAggregateFactory implements AggregateFactory
{
    /** @var string the name of the method to call on the Aggregate */
    private $staticConstructorMethod;

    public function __construct($staticConstructorMethod = 'instantiateForReconstitution')
    {
        $this->staticConstructorMethod = $staticConstructorMethod;
    }

    /** {@inheritDoc} */
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream)
    {
        Assert::true(method_exists($aggregateClass, $this->staticConstructorMethod));

        $methodCall = sprintf('%s::%s', $aggregateClass, $this->staticConstructorMethod);
        $aggregate = call_user_func($methodCall);

        Assert::isInstanceOf($aggregate, $aggregateClass);

        $aggregate->initializeState($domainEventStream);
        return $aggregate;
    }
}
