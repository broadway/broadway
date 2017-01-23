<?php

namespace Broadway\EventStore\Exception;

use Broadway\Domain\DomainEventStreamInterface;
use Broadway\EventStore\EventStoreException;
use Exception;

class DuplicatePlayheadException extends EventStoreException
{
    /**
     * @var DomainEventStreamInterface
     */
    private $eventStream;

    /**
     * @param DomainEventStreamInterface $eventStream
     * @param Exception                  $previous
     */
    public function __construct(DomainEventStreamInterface $eventStream, $previous = null)
    {
        parent::__construct(null, 0, $previous);

        $this->eventStream = $eventStream;
    }

    /**
     * @return DomainEventStreamInterface
     */
    public function getEventStream()
    {
        return $this->eventStream;
    }
}
