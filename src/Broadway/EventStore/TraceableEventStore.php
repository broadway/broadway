<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;

/**
 * Event store that is able to record all appended events.
 */
final class TraceableEventStore implements EventStore
{
    private $eventStore;
    private $recorded = [];
    private $tracing = false;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStream $eventStream): void
    {
        $this->eventStore->append($id, $eventStream);

        if (!$this->tracing) {
            return;
        }

        foreach ($eventStream as $event) {
            $this->recorded[] = $event;
        }
    }

    /**
     * @return mixed[] Appended events
     */
    public function getEvents(): array
    {
        return array_map(
            function (DomainMessage $message) {
                return $message->getPayload();
            },
            $this->recorded
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): DomainEventStream
    {
        return $this->eventStore->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayheadToPlayhead($id, int $fromPlayhead, int $toPlayhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayheadToPlayhead($id, $fromPlayhead, $toPlayhead);
    }

    /**
     * Start tracing.
     */
    public function trace(): void
    {
        $this->tracing = true;
    }

    /**
     * Clear any previously recorded events.
     */
    public function clearEvents(): void
    {
        $this->recorded = [];
    }
}
