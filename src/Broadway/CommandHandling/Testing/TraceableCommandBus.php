<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\DispatchesCommands;
use Broadway\CommandHandling\HandlesCommands;

/**
 * Command bus that is able to record all dispatched commands.
 */
class TraceableCommandBus implements DispatchesCommands
{
    private $commandHandlers = array();
    private $commands        = array();
    private $record          = false;

    /**
     * {@inheritDoc}
     */
    public function subscribe(HandlesCommands $handler)
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        if (! $this->record) {
            return;
        }

        $this->commands[] = $command;
    }

    /**
     * @return array
     */
    public function getRecordedCommands()
    {
        return $this->commands;
    }

    public function record()
    {
        return $this->record = true;
    }
}
