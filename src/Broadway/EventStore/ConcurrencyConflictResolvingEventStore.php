<?php
namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\Exception\DuplicatePlayheadException;

class ConcurrencyConflictResolvingEventStore implements EventStoreInterface
{
    /** @var EventStoreInterface */
    private $eventStore;

    /** @var ConcurrencyConflictResolver */
    private $conflictResolver;

    /**
     * ConcurrencyConflictResolvingEventStore constructor.
     *
     * @param EventStoreInterface         $eventStore
     * @param ConcurrencyConflictResolver $conflictResolver
     */
    public function __construct(EventStoreInterface $eventStore, ConcurrencyConflictResolver $conflictResolver)
    {
        $this->eventStore       = $eventStore;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * @inheritDoc
     */
    public function append($id, DomainEventStreamInterface $uncommittedEvents)
    {
        try {
            $this->eventStore->append($id, $uncommittedEvents);
        } catch (DuplicatePlayheadException $e) {
            $committedEvents   = $this->eventStore->load($id);
            $conflictingEvents = $this->getConflictingEvents($uncommittedEvents, $committedEvents);

            $conflictResolvedEvents = [];
            $playhead               = $this->getCurrentPlayhead($committedEvents);

            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                foreach ($conflictingEvents as $conflictingEvent) {
                    if ($this->conflictResolver->conflictsWith($conflictingEvent, $uncommittedEvent)) {
                        throw $e;
                    }
                }

                $playhead++;

                $conflictResolvedEvents[] = new DomainMessage(
                    $id,
                    $playhead,
                    $uncommittedEvent->getMetadata(),
                    $uncommittedEvent->getPayload(),
                    $uncommittedEvent->getRecordedOn());
            }

            $this->append($id, new DomainEventStream($conflictResolvedEvents));
        }
    }

    /**
     * @inheritDoc
     */
    public function load($id)
    {
        $this->eventStore->load($id);
    }

    /**
     * @return int
     */
    private function getCurrentPlayhead(DomainEventStreamInterface $committedEvents)
    {
        $playhead = 0;

        foreach ($committedEvents as $committedEvent) {
            /** @var DomainMessage $committedEvent */
            $playhead = $committedEvent->getPlayhead();
        }

        return $playhead;
    }

    /**
     * @return DomainMessage[]
     */
    private function getConflictingEvents(
        DomainEventStreamInterface $uncommittedEvents,
        DomainEventStreamInterface $committedEvents
    ) {
        $conflictingEvents = [];

        /** @var DomainMessage $committedEvent */
        foreach ($committedEvents as $committedEvent) {
            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                if ($committedEvent->getPlayhead() >= $uncommittedEvent->getPlayhead()) {
                    $conflictingEvents[] = $committedEvent;

                    break;
                }
            }
        }

        return $conflictingEvents;
    }
}