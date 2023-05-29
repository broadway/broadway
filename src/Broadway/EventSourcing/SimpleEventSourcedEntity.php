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

/**
 * Convenience base class for event sourced entities.
 */
abstract class SimpleEventSourcedEntity implements EventSourcedEntity
{
    /**
     * @var EventSourcedAggregateRoot|null
     */
    private $aggregateRoot;

    public function handleRecursively($event): void
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this->aggregateRoot);
            $entity->handleRecursively($event);
        }
    }

    public function registerAggregateRoot(EventSourcedAggregateRoot $aggregateRoot): void
    {
        if (null !== $this->aggregateRoot && $this->aggregateRoot !== $aggregateRoot) {
            throw new AggregateRootAlreadyRegisteredException();
        }

        $this->aggregateRoot = $aggregateRoot;
    }

    protected function apply($event): void
    {
        $this->aggregateRoot->apply($event);
    }

    /**
     * Handles event if capable.
     */
    protected function handle($event): void
    {
        $method = $this->getApplyMethod($event);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($event);
    }

    /**
     * Returns all child entities.
     *
     * @return EventSourcedEntity[]
     */
    protected function getChildEntities(): array
    {
        return [];
    }

    private function getApplyMethod($event): string
    {
        $classParts = explode('\\', get_class($event));

        return 'apply'.end($classParts);
    }
}
