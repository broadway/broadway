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

/**
 * Event dispatcher implementation.
 */
class CallableEventDispatcher implements EventDispatcher
{
    private $listeners = [];

    /**
     * {@inheritDoc}
     */
    public function dispatch($eventName, array $arguments)
    {
        if (! isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addListener($eventName, /* callable */ $callable)
    {
        if (! isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = $callable;
    }
}
