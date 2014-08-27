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
 * Convenience base class for command handlers.
 *
 * Command handlers using this base class will implement `handle<CommandName>`
 * methods for each command they can handle.
 *
 * Note: the convention used does not take namespaces into account.
 */
abstract class CommandHandler implements HandlesCommands
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
        $classParts = explode('\\', get_class($command));

        return 'handle' . end($classParts);
    }
}
