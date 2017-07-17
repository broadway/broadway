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

use Broadway\CommandHandling\Exception\ClosureParameterNotAnObjectException;
use Broadway\CommandHandling\Exception\CommandNotAnObjectException;

/**
 * Using this class command handlers can be registered with closures
 */
class ClosureCommandHandler implements CommandHandler
{
    /**
     * @var \Closure[]
     */
    private $handlers = [];

    /**
     * @param \Closure $handler
     */
    public function add(\Closure $handler)
    {
        $reflection = new \ReflectionFunction($handler);
        $reflectionParams = $reflection->getParameters();

        if(!isset($reflectionParams[0]) || !$reflectionParams[0]->getClass()) {
            throw new ClosureParameterNotAnObjectException();
        }

        $index = $reflectionParams[0]->getClass()->getName();

        $this->handlers[$index] = $handler;
    }

    /**
     * @param mixed $command
     */
    public function handle($command)
    {
        if (!is_object($command)) {
            throw new CommandNotAnObjectException();
        }

        $index = get_class($command);

        if(!isset($this->handlers[$index])) {
            return;
        }

        $this->handlers[$index]($command);
    }
}
