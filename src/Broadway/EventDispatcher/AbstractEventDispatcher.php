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
abstract class AbstractEventDispatcher
{
    /**
     * @param string $eventName
     * @param array  $arguments
     */
    abstract public function dispatch($eventName, array $arguments);

    /**
     * @param string   $eventName
     * @param callable $callable
     */
    abstract public function addListener($eventName, /* callable */ $callable);
}
