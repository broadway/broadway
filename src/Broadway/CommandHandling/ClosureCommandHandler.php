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
use ReflectionClass;

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
        $reflectionParams = $reflection->getParameters();

        $reflectionType = $reflectionParams[0]->getType();

        $name = null;
        if ($reflectionType instanceof \ReflectionNamedType) {
            /** @var \ReflectionNamedType $reflectionNamed */
            $reflectionNamed = $reflectionParams[0]->getType();
            if (!$reflectionNamed->isBuiltin()) {
                $name = new ReflectionClass($reflectionNamed->getName());
            }
        }

        if (!$name) {
            throw new ClosureParameterNotAnObjectException();
        }

        $this->handlers[$name->getName()] = $handler;
    }

    /**
     * @param mixed $command
     */
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
