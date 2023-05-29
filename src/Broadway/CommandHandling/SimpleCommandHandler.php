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
    public function handle($command): void
    {
        $method = $this->getHandleMethod($command);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($command);
    }

    private function getHandleMethod($command): string
    {
        if (!is_object($command)) {
            throw new CommandNotAnObjectException();
        }

        $classParts = explode('\\', get_class($command));

        return 'handle'.end($classParts);
    }
}
