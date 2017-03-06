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

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;

/**
 * Event store that is able to record all appended events.
 */
class TraceableEventStore implements EventStore
{
    private $eventStore;
    private $recorded = [];
    private $tracing  = false;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, DomainEventStream $eventStream)
    {
        $this->eventStore->append($id, $eventStream);

        if (! $this->tracing) {
            return;
        }

        foreach ($eventStream as $event) {
            $this->recorded[] = $event;
        }
    }

    /**
     * @return array Appended events
     */
    public function getEvents()
    {
        return array_map(
            function (DomainMessage $message) {
                return $message->getPayload();
            },
            $this->recorded
        );
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        return $this->eventStore->load($id);
    }

    /**
     * {@inheritDoc}
     */
    public function loadFromPlayhead($id, $playhead)
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    /**
     * Start tracing.
     */
    public function trace()
    {
        $this->tracing = true;
    }

    /**
     * Clear any previously recorded events.
     */
    public function clearEvents()
    {
        $this->recorded = [];
    }
}
