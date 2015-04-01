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

use MongoException;

/**
 * Wraps exceptions thrown by the MongoDB event store.
 */
class MongoDBEventStoreException extends EventStoreException
{
    public static function create(MongoException $exception)
    {
        return new static(null, 0, $exception);
    }
}
