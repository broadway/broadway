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

namespace Broadway\EventDispatcher;

/**
 * Event dispatcher implementation.
 */
final class CallableEventDispatcher implements EventDispatcher
{
    private $listeners = [];

    public function dispatch(string $eventName, array $arguments): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }

    public function addListener(string $eventName, callable $callable): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = $callable;
    }
}
