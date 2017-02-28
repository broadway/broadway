<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;

/**
 * Publishes events to the subscribed event listeners.
 */
interface EventBus
{
    /**
     * Subscribes the event listener to the event bus.
     *
     * @param EventListener $eventListener
     */
    public function subscribe(EventListener $eventListener);

    /**
     * Publishes the events from the domain event stream to the listeners.
     *
     * @param DomainEventStream $domainMessages
     */
    public function publish(DomainEventStream $domainMessages);
}
