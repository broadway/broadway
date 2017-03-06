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
 * Base type for an event dispatcher.
 */
interface EventDispatcher
{
    /**
     * @param string $eventName
     * @param array  $arguments
     */
    public function dispatch($eventName, array $arguments);

    /**
     * @param string   $eventName
     * @param callable $callable
     */
    public function addListener($eventName, /* callable */ $callable);
}
