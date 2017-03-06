<?php

namespace Broadway\EventStore\Exception;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStoreException;
use Exception;

class DuplicatePlayheadException extends EventStoreException
{
    /**
     * @var DomainEventStream
     */
    private $eventStream;

    /**
     * @param DomainEventStream $eventStream
     * @param Exception         $previous
     */
    public function __construct(DomainEventStream $eventStream, $previous = null)
    {
        parent::__construct(null, 0, $previous);

        $this->eventStream = $eventStream;
    }

    /**
     * @return DomainEventStream
     */
    public function getEventStream()
    {
        return $this->eventStream;
    }
}
