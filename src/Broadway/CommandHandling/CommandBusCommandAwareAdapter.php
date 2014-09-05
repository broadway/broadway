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
 * Implementation that uses the adapter pattern which lets us use a command bus which is not command aware
 */
class CommandBusCommandAwareAdapter implements CommandBusCommandAwareInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * Constructor.
     *
     * @param CommandBusInterface $commandBus A command bus which is not command aware instance.
     */
    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(CommandInterface $command)
    {
        $this->commandBus->dispatch($command);
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(CommandHandlerInterface $handler)
    {
        $this->commandBus->subscribe($handler);
    }
}
