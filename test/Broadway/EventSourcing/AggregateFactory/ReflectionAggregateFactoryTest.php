<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\TestCase;

/**
 *
 */
final class ReflectionAggregateFactoryTest extends TestCase
{
    /**
     * @var ReflectionAggregateFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new ReflectionAggregateFactory();
    }

    /**
     * @test
     */
    public function it_creates_instance_of_aggregate_with_private_constructor()
    {
        $aggregate = $this->factory->create(
            TestAggregateWithPrivateConstructor::class,
            new DomainEventStream([])
        );

        $this->assertInstanceOf(TestAggregateWithPrivateConstructor::class, $aggregate);
    }

    /**
     * @test
     */
    public function it_creates_instance_of_aggregate_with_public_constructor()
    {
        $aggregate = $this->factory->create(
            TestAggregateWithPublicConstructor::class,
            new DomainEventStream([])
        );

        $this->assertInstanceOf(TestAggregateWithPublicConstructor::class, $aggregate);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Impossible to initialize "stdClass"
     */
    public function it_does_not_handle_weird_classes()
    {
        $this->factory->create(\stdClass::class, new DomainEventStream([]));
    }
}

final class TestAggregateWithPrivateConstructor extends EventSourcedAggregateRoot
{
    private function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregateRootId()
    {
        return 'foo42';
    }
}

final class TestAggregateWithPublicConstructor extends EventSourcedAggregateRoot
{
    /**
     * {@inheritDoc}
     */
    public function getAggregateRootId()
    {
        return 'foo42';
    }
}
