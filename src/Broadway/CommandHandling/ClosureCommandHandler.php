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

use Broadway\CommandHandling\Exception\ClosureParameterNotAnObjectException;
use Broadway\CommandHandling\Exception\CommandNotAnObjectException;

/**
 * Using this class command handlers can be registered with closures.
 */
class ClosureCommandHandler implements CommandHandler
{
    /**
     * @var \Closure[]
     */
    private $handlers = [];

    public function add(\Closure $handler): void
    {
        $reflection = new \ReflectionFunction($handler);
        if (0 === $reflection->getNumberOfParameters()) {
            throw new ClosureParameterNotAnObjectException();
        }

        $reflectionType = $reflection->getParameters()[0]->getType();
        if ($reflectionType instanceof \ReflectionNamedType && !$reflectionType->isBuiltin()) {
            $this->handlers[$reflectionType->getName()] = $handler;
        } else {
            throw new ClosureParameterNotAnObjectException();
        }
    }

    public function handle($command): void
    {
        if (!is_object($command)) {
            throw new CommandNotAnObjectException();
        }

        $index = get_class($command);

        if (!isset($this->handlers[$index])) {
            return;
        }

        $this->handlers[$index]($command);
    }
}
