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

use Broadway\EventDispatcher\EventDispatcher;

/**
 * Command bus decorator that dispatches events.
 *
 * Dispatches events signalling whether a command was executed successfully or
 * if it failed.
 */
final class EventDispatchingCommandBus implements CommandBus
{
    public const EVENT_COMMAND_SUCCESS = 'broadway.command_handling.command_success';
    public const EVENT_COMMAND_FAILURE = 'broadway.command_handling.command_failure';

    private $commandBus;
    private $dispatcher;

    public function __construct(CommandBus $commandBus, EventDispatcher $dispatcher)
    {
        $this->commandBus = $commandBus;
        $this->dispatcher = $dispatcher;
    }

    public function dispatch($command): void
    {
        try {
            $this->commandBus->dispatch($command);
            $this->dispatcher->dispatch(self::EVENT_COMMAND_SUCCESS, ['command' => $command]);
        } catch (\Exception $e) {
            $this->dispatcher->dispatch(
                self::EVENT_COMMAND_FAILURE,
                ['command' => $command, 'exception' => $e]
            );

            throw $e;
        }
    }

    public function subscribe(CommandHandler $handler): void
    {
        $this->commandBus->subscribe($handler);
    }
}
