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

use Broadway\EventDispatcher\EventDispatcherInterface;
use Exception;

/**
 * Command bus decorator that dispatches events.
 *
 * Dispatches events signalling whether a command was executed successfully or
 * if it failed.
 */
class EventDispatchingCommandBus implements CommandBusInterface
{
    const EVENT_COMMAND_SUCCESS = 'broadway.command_handling.command_success';
    const EVENT_COMMAND_FAILURE = 'broadway.command_handling.command_failure';

    private $commandBus;
    private $dispatcher;

    public function __construct(CommandBusInterface $commandBus, EventDispatcherInterface $dispatcher)
    {
        $this->commandBus = $commandBus;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        try {
            $this->commandBus->dispatch($command);
            $this->dispatcher->dispatch(self::EVENT_COMMAND_SUCCESS, ['command' => $command]);
        } catch (Exception $e) {
            $this->dispatcher->dispatch(
                self::EVENT_COMMAND_FAILURE,
                ['command' => $command, 'exception' => $e]
            );

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(CommandHandlerInterface $handler)
    {
        $this->commandBus->subscribe($handler);
    }
}
