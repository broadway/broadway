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

namespace Broadway\Repository;

use Exception;
use RuntimeException;

/**
 * Exception thrown when an aggregate is not found.
 */
final class AggregateNotFoundException extends RuntimeException
{
    /**
     * @param mixed     $id
     * @param Exception $previous
     *
     * @return AggregateNotFoundException
     */
    public static function create($id, Exception $previous = null): self
    {
        return new self(sprintf("Aggregate with id '%s' not found", $id), 0, $previous);
    }
}
