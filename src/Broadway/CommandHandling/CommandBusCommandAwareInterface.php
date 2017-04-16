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
 * A command bus which is aware that the given commands are actually commands.
 *
 * This means they have to implement the right interface.
 */
interface CommandBusCommandAwareInterface
{
    /**
     * Dispatches your command object to the command handler.
     *
     * @param CommandInterface $command
     */
    public function dispatch(CommandInterface $command);

    /**
     * Subscribes the command handler to this CommandBus
     */
    public function subscribe(CommandHandlerInterface $handler);
}
