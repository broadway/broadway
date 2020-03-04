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

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;

/**
 * Publishes events to the subscribed event listeners.
 */
interface EventBus
{
    /**
     * Subscribes the event listener to the event bus.
     */
    public function subscribe(EventListener $eventListener): void;

    /**
     * Publishes the events from the domain event stream to the listeners.
     */
    public function publish(DomainEventStream $domainMessages): void;
}
