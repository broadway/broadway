<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *
 */

namespace Broadway\Snapshotting;

use Exception;
use RuntimeException;

class SnapshotNotFoundException extends RuntimeException
{
    /**
     * @param mixed     $id
     * @param Exception $previous
     */
    public static function create($id, Exception $previous = null)
    {
        return new self(
            sprintf("Snapshot for id '%s' not found.", $id),
            0,
            $previous
        );
    }
}
