<?php

declare(strict_types=1);

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling\Exception;

/**
 * Closure parameter should be object.
 */
class ClosureParameterNotAnObjectException extends \InvalidArgumentException
{
}
