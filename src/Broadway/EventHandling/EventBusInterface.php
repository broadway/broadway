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
interface EventBusInterface
{
    /**
     * Subscribes the event listener to the event bus.
     */
    public function subscribe(EventListenerInterface $eventListener);

    /**
     * Publishes the events from the domain event stream to the listeners.
     */
    public function publish(DomainEventStream $domainMessages);
}
