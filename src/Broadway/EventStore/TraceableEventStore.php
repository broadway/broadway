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

use Broadway\Domain\StreamsDomainEvents;

/**
 * Event store that is able to record all appended events.
 */
class TraceableEventStore implements EventStore
{
    private $eventStore;
    private $recorded = array();
    private $tracing  = false;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, StreamsDomainEvents $eventStream)
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
            function ($message) {
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
     * Start tracing.
     */
    public function trace()
    {
        $this->tracing = true;
    }
}
