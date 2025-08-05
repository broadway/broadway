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

namespace Broadway\EventSourcing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SimpleEventSourcedEntityTest extends TestCase
{
    #[Test]
    public function it_handles_events_recursively()
    {
        $aggregateRoot = new Aggregate();
        $child = new Entity();

        $aggregateRoot->addChildEntity($child);

        $child->addChildEntity(new Entity());

        $aggregateRoot->doApply();
    }

    #[Test]
    public function it_applies_events_to_aggregate_root()
    {
        $aggregateRoot = new Aggregate();

        $child = new Entity();
        $grandChild = new Entity();

        $aggregateRoot->addChildEntity($child);

        $child->addChildEntity($grandChild);
        $aggregateRoot->doHandleRecursively();  // Initialize tree structure

        $grandChild->doApply();
    }

    #[Test]
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
    private array $children = [];

    protected function getChildEntities(): array
    {
        return $this->children;
    }

    public function addChildEntity($entity): void
    {
        $this->children[] = $entity;
    }

    public function doApply(): void
    {
        $this->apply(new Event());
    }

    public function doHandleRecursively(): void
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
    private array $children = [];

    protected function getChildEntities(): array
    {
        return $this->children;
    }

    public function addChildEntity($entity): void
    {
        $this->children[] = $entity;
    }

    protected function applyEvent($event): void
    {
    }

    public function doApply(): void
    {
        $this->apply(new Event());
    }
}

class Event
{
}
