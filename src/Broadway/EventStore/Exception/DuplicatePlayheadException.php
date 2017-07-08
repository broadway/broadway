<?php

declare(strict_types=1);

namespace Broadway\EventStore\Exception;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStoreException;
use Exception;

final class DuplicatePlayheadException extends EventStoreException
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
        parent::__construct('', 0, $previous);

        $this->eventStream = $eventStream;
    }

    /**
     * @return DomainEventStream
     */
    public function getEventStream(): DomainEventStream
    {
        return $this->eventStream;
    }
}
