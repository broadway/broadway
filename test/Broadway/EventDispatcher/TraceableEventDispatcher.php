<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventDispatcher;

class TraceableEventDispatcher implements EventDispatcherInterface
{
    private $dispatchedEvents = array();

    public function dispatch($eventName, array $arguments)
    {
        $this->dispatchedEvents[] = array('event' => $eventName, 'arguments' => $arguments);
    }

    public function addListener($eventName, /* callable */ $callable)
    {
        return;
    }

    public function getDispatchedEvents()
    {
        return $this->dispatchedEvents;
    }
}
