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

use Broadway\Domain\DomainEventStream;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use PHPUnit\Framework\TestCase;

final class ReflectionAggregateFactoryTest extends TestCase
{
    /**
     * @var ReflectionAggregateFactory
     */
    private $factory;

    protected function setUp(): void
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
     */
    public function it_does_not_handle_weird_classes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Impossible to initialize "%s"', \stdClass::class));

        $this->factory->create(\stdClass::class, new DomainEventStream([]));
    }
}

final class TestAggregateWithPrivateConstructor extends EventSourcedAggregateRoot
{
    private function __construct()
    {
    }

    public function getAggregateRootId(): string
    {
        return 'foo42';
    }
}

final class TestAggregateWithPublicConstructor extends EventSourcedAggregateRoot
{
    public function getAggregateRootId(): string
    {
        return 'foo42';
    }
}
