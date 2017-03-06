<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing;

use Broadway\TestCase;

class SimpleEventSourcedEntityTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_events_recursively()
    {
        $aggregateRoot = new Aggregate();
        $child         = new Entity();

        $aggregateRoot->addChildEntity($child);

        $mock = $this->getMock('Broadway\EventSourcing\Entity', ['handleRecursively']);
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
        $aggregateRoot = $this->getMock('Broadway\EventSourcing\Aggregate', ['apply']);
        $aggregateRoot->expects($this->once())
            ->method('apply');

        $child         = new Entity();
        $grandChild    = new Entity();

        $aggregateRoot->addChildEntity($child);

        $child->addChildEntity($grandChild);
        $aggregateRoot->doHandleRecursively();  // Initialize tree structure

        $grandChild->doApply();
    }

    /**
     * @test
     * @expectedException Broadway\EventSourcing\AggregateRootAlreadyRegisteredException
     */
    public function it_can_only_have_one_root()
    {
        $root1 = new Aggregate();
        $root2 = new Aggregate();

        $entity = new Entity();

        $root1->addChildEntity($entity);
        $root2->addChildEntity($entity);

        $root1->doHandleRecursively();
        $root2->doHandleRecursively();
    }
}

class Aggregate extends EventSourcedAggregateRoot
{
    private $children = [];

    protected function getChildEntities()
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

    public function getAggregateRootId()
    {
    }
}

class Entity extends SimpleEventSourcedEntity
{
    private $children = [];

    protected function getChildEntities()
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
