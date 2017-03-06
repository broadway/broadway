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

/**
 * Convenience base class for event sourced entities.
 */
abstract class SimpleEventSourcedEntity implements EventSourcedEntity
{
    /**
     * @var EventSourcedAggregateRoot|null
     */
    private $aggregateRoot;

    /**
     * {@inheritDoc}
     */
    public function handleRecursively($event)
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this->aggregateRoot);
            $entity->handleRecursively($event);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function registerAggregateRoot(EventSourcedAggregateRoot $aggregateRoot)
    {
        if (null !== $this->aggregateRoot && $this->aggregateRoot !== $aggregateRoot) {
            throw new AggregateRootAlreadyRegisteredException();
        }

        $this->aggregateRoot = $aggregateRoot;
    }

    protected function apply($event)
    {
        $this->aggregateRoot->apply($event);
    }

    /**
     * Handles event if capable.
     *
     * @param $event
     */
    protected function handle($event)
    {
        $method = $this->getApplyMethod($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->$method($event);
    }

    /**
     * Returns all child entities
     *
     * @return EventSourcedEntity[]
     */
    protected function getChildEntities()
    {
        return [];
    }

    private function getApplyMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}
