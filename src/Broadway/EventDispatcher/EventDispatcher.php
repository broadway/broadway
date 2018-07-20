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

namespace Broadway\EventDispatcher;

/**
 * Base type for an event dispatcher.
 */
interface EventDispatcher
{
    public function dispatch(string $eventName, array $arguments): void;

    public function addListener(string $eventName, callable $callable): void;
}
