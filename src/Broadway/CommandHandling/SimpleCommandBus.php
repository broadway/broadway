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
 * Simple synchronous dispatching of commands.
 */
class SimpleCommandBus implements CommandBus
{
    private $commandHandlers = [];
    private $queue           = [];
    private $isDispatching   = false;

    /**
     * {@inheritDoc}
     */
    public function subscribe(CommandHandler $handler)
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        $this->queue[] = $command;

        if (! $this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->queue)) {
                    foreach ($this->commandHandlers as $handler) {
                        $handler->handle($command);
                    }
                }

                $this->isDispatching = false;
            } catch (\Exception $e) {
                $this->isDispatching = false;
                throw $e;
            }
        }
    }
}
