<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Doctrine\DBAL\DBALException;

/**
 * Wraps exceptions thrown by the DBAL event store.
 */
class DBALEventStoreException extends EventStoreException
{
    /**
     * @param DBALException $exception
     * @return DBALEventStoreException
     */
    public static function create(DBALException $exception)
    {
        return new DBALEventStoreException(null, 0, $exception);
    }
}
