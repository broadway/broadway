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

namespace Broadway\EventSourcing;

use PHPUnit\Framework\TestCase;

class SimpleEventSourcedEntityTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_events_recursively()
    {
        $aggregateRoot = new Aggregate();
        $child = new Entity();

        $aggregateRoot->addChildEntity($child);

        $mock = $this->getMockBuilder('Broadway\EventSourcing\Entity')
            ->setMethods(['handleRecursively'])
            ->getMock();

        $mock->expects($this->once())
            ->method('handleRecursively');

        $child->addChildEntity($mock);

        $aggregateRoot->doApply();
    }

    /**
     * @test
     */
    public function it_applies_events_to_aggregate_root()
    {
        $aggregateRoot = $this->getMockBuilder('Broadway\EventSourcing\Aggregate')
            ->setMethods(['apply'])
            ->getMock();

        $aggregateRoot->expects($this->once())
            ->method('apply');

        $child = new Entity();
        $grandChild = new Entity();

        $aggregateRoot->addChildEntity($child);

        $child->addChildEntity($grandChild);
        $aggregateRoot->doHandleRecursively();  // Initialize tree structure

        $grandChild->doApply();
    }

    /**
     * @test
     */
    public function it_can_only_have_one_root()
    {
        $root1 = new Aggregate();
        $root2 = new Aggregate();

        $entity = new Entity();

        $root1->addChildEntity($entity);
        $root2->addChildEntity($entity);

        $this->expectException(AggregateRootAlreadyRegisteredException::class);

        $root1->doHandleRecursively();
        $root2->doHandleRecursively();
    }
}

class Aggregate extends EventSourcedAggregateRoot
{
    private $children = [];

    protected function getChildEntities(): array
    {
        return $this->children;
    }

    public function addChildEntity($entity)
    {
        $this->children[] = $entity;
    }

    public function doApply()
    {
        $this->apply(new Event());
    }

    public function doHandleRecursively()
    {
        $this->handleRecursively(new Event());
    }

    public function getAggregateRootId(): string
    {
        return '42';
    }
}

class Entity extends SimpleEventSourcedEntity
{
    private $children = [];

    protected function getChildEntities(): array
    {
        return $this->children;
    }

    public function addChildEntity($entity)
    {
        $this->children[] = $entity;
    }

    protected function applyEvent($event)
    {
    }

    public function doApply()
    {
        $this->apply(new Event());
    }
}

class Event
{
}
