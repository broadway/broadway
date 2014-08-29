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

use Broadway\Domain\AggregateRoot as AggregateRootInterface;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;

/**
 * Convenience base class for event sourced aggregate roots.
 */
abstract class EventSourcedAggregateRoot implements AggregateRootInterface
{
    /**
     * @var array
     */
    private $uncommittedEvents = array();
    private $playhead          = -1; // 0-based playhead allows events[0] to contain playhead 0

    /**
     * Applies an event. The event is added to the AggregateRoot's list of uncommited events.
     *
     * @param $event
     * @internal
     */
    public function apply($event)
    {
        $this->handleRecursively($event);

        $this->playhead++;
        $this->uncommittedEvents[] = DomainMessage::recordNow(
            $this->getId(),
            $this->playhead,
            new Metadata(array()),
            $event
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getUncommittedEvents()
    {
        $stream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = array();

        return $stream;
    }

    /**
     * Reconstitute the aggregate from an event stream.
     *
     * @return EventSourcedAggregateRoot
     */
    public static function reconstituteFromDomainEventStream(DomainEventStream $stream)
    {
        $instance = static::instantiateForReconstitution();

        $instance->initializeState($stream);

        return $instance;
    }

    /**
     * Used to instantiate an aggregate for the purpose of reconstitution.
     *
     * The default implementation will allow for calling an empty constructor.
     * This will currently work on any EventSourcedAggregateRoot class that has
     * no constructor, a public constructor, or a protected constructor.
     *
     * If an EventSourcedAggregateRoot has a private constructor the
     * EventSourcedAggregateRoot should override this method and call the
     * private constructor itself.
     *
     * @return EventSourcedAggregateRoot
     */
    protected static function instantiateForReconstitution()
    {
        return new static;
    }

    /**
     * Initializes the aggregate using the given "history" of events.
     */
    protected function initializeState(DomainEventStream $stream)
    {
        foreach ($stream as $message) {
            $this->playhead++;
            $this->handleRecursively($message->getPayload());
        }
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
     * {@inheritDoc}
     */
    protected function handleRecursively($event)
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this);
            $entity->handleRecursively($event);
        }
    }

    /**
     * Returns all child entities
     *
     * Override this method if your aggregate root contains child entities.
     *
     * @return array
     */
    protected function getChildEntities()
    {
        return array();
    }

    private function getApplyMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}
