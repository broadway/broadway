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

use Broadway\CommandHandling\Exception\CommandNotAnObjectException;

/**
 * Convenience base class for command handlers.
 *
 * Command handlers using this base class will implement `handle<CommandName>`
 * methods for each command they can handle.
 *
 * Note: the convention used does not take namespaces into account.
 */
abstract class SimpleCommandHandler implements CommandHandler
{
    /**
     * {@inheritDoc}
     */
    public function handle($command)
    {
        $method = $this->getHandleMethod($command);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->$method($command);
    }

    private function getHandleMethod($command)
    {
        if (! is_object($command)) {
            throw new CommandNotAnObjectException();
        }

        $classParts = explode('\\', get_class($command));

        return 'handle' . end($classParts);
    }
}
