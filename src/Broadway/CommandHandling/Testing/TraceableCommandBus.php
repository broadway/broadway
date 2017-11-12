<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\CommandBus;
use Broadway\CommandHandling\CommandHandler;

/**
 * Command bus that is able to record all dispatched commands.
 */
final class TraceableCommandBus implements CommandBus
{
    private $commandHandlers = [];
    private $commands = [];
    private $record = false;

    /**
     * {@inheritdoc}
     */
    public function subscribe(CommandHandler $handler)
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($command)
    {
        if (!$this->record) {
            return;
        }

        $this->commands[] = $command;
    }

    public function getRecordedCommands(): array
    {
        return $this->commands;
    }

    public function record(): bool
    {
        return $this->record = true;
    }
}
