<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventDispatcher\Testing;

use Broadway\EventDispatcher\EventDispatcher;

final class TraceableEventDispatcher implements EventDispatcher
{
    private $dispatchedEvents = [];

    public function dispatch(string $eventName, array $arguments)
    {
        $this->dispatchedEvents[] = ['event' => $eventName, 'arguments' => $arguments];
    }

    public function addListener(string $eventName, callable $callable)
    {
        return;
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
