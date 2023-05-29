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

namespace Broadway\Repository;

use Exception;

/**
 * Exception thrown when an aggregate is not found.
 */
final class AggregateNotFoundException extends \RuntimeException
{
    /**
     * @param \Exception $previous
     */
    public static function create($id, \Exception $previous = null): self
    {
        return new self(sprintf("Aggregate with id '%s' not found", $id), 0, $previous);
    }
}
