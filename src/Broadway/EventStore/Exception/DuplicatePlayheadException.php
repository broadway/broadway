<?php

namespace Broadway\EventStore\Exception;

use Broadway\EventStore\EventStoreException;
use Exception;

class DuplicatePlayheadException extends EventStoreException
{
    public static function create(Exception $exception)
    {
        return new self(null, 0, $exception);
    }
}
