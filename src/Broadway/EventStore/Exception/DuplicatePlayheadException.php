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

namespace MicroModule\Broadway\EventStore\Exception;

use MicroModule\Broadway\Domain\DomainEventStream;
use MicroModule\Broadway\EventStore\EventStoreException;
use Exception;

final class DuplicatePlayheadException extends EventStoreException
{
    /**
     * @var DomainEventStream
     */
    private $eventStream;

    /**
     * @param Exception $previous
     */
    public function __construct(DomainEventStream $eventStream, $previous = null)
    {
        parent::__construct('', 0, $previous);

        $this->eventStream = $eventStream;
    }

    public function getEventStream(): DomainEventStream
    {
        return $this->eventStream;
    }
}
