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

namespace Broadway\CommandHandling;

/**
 * Dispatches command objects to the subscribed command handlers.
 */
interface CommandBus
{
    /**
     * Dispatches the command $command to the proper CommandHandler.
     */
    public function dispatch($command): void;

    /**
     * Subscribes the command handler to this CommandBus.
     */
    public function subscribe(CommandHandler $handler): void;
}
