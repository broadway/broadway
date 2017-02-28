<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling;

/**
 * Dispatches command objects to the subscribed command handlers.
 */
interface CommandBus
{
    /**
     * Dispatches the command $command to the proper CommandHandler
     *
     * @param mixed $command
     */
    public function dispatch($command);

    /**
     * Subscribes the command handler to this CommandBus
     */
    public function subscribe(CommandHandler $handler);
}
