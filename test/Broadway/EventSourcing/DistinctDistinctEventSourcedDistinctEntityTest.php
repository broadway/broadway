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

use Broadway\Domain\DistinctEntityEvent;
use PHPUnit\Framework\TestCase;

class DistinctEventSourcedEntityTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_only_entities_with_particular_id()
    {
        $aggregate = new TheAggregate();
        $entityA = new DistinctEntity('A');
        $entityB = new DistinctEntity('B');
        $aggregate->addChildEntity($entityA);
        $aggregate->addChildEntity($entityB);

        $event = new DistinctEvent('A');
        $aggregate->apply($event);

        $this->assertEquals(1, $entityA->getAppliedEventsNo());
        $this->assertEquals(0, $entityB->getAppliedEventsNo());
    }

    /**
     * @test
     */
    public function it_handles_nested_entities()
    {
        $aggregate = new TheAggregate();
        $entity1 = new DistinctEntity('1');
        $entity1A = new DistinctEntity('1A');
        $entity1->addChildEntity($entity1A);
        $aggregate->addChildEntity($entity1);

        $event = new DistinctEvent('1A');
        $aggregate->apply($event);

        $this->assertEquals(0, $entity1->getAppliedEventsNo());
        $this->assertEquals(1, $entity1A->getAppliedEventsNo());
    }
}

class TheAggregate extends EventSourcedAggregateRoot
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

    public function getAggregateRootId(): string
    {
        return '42';
    }
}

class DistinctEntity extends DistinctEventSourcedEntity
{
    private $children = [];

    private $id;

    private $appliedEventsNo = 0;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getEntityId(): string
    {
        return (string) $this->id;
    }

    protected function getChildEntities(): array
    {
        return $this->children;
    }

    public function addChildEntity($entity)
    {
        $this->children[] = $entity;
    }

    protected function applyDistinctEvent($event)
    {
        ++$this->appliedEventsNo;
    }

    public function getAppliedEventsNo(): int
    {
        return $this->appliedEventsNo;
    }
}

class DistinctEvent implements DistinctEntityEvent
{
    /**
     * @var string
     */
    private $distinctEntityId;

    public function __construct(string $distinctEntityId)
    {
        $this->distinctEntityId = $distinctEntityId;
    }

    public function getDistinctEntityId(): string
    {
        return $this->distinctEntityId;
    }
}
