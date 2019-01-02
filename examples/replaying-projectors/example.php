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

require_once __DIR__.'/../bootstrap.php';

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListener;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

$eventStore = new InMemoryEventStore();

$aggregateId = 'aggregate_to_replay';

class SimpleEvent
{
}

// First, we fill up the event store with the events of two aggregates
$domainEventStream = new DomainEventStream([
    DomainMessage::recordNow($aggregateId, 0, new Metadata(), new SimpleEvent()),
    DomainMessage::recordNow($aggregateId, 1, new Metadata(), new SimpleEvent()),
]);

$eventStore->append($aggregateId, $domainEventStream);

$secondAggregateId = 'do_not_replay_this_one';

$domainEventStream = new DomainEventStream([
    DomainMessage::recordNow($secondAggregateId, 0, new Metadata(), new SimpleEvent()),
]);

$eventStore->append($secondAggregateId, $domainEventStream);

// Now, we define a Replayer class. This example allows the replaying of a
// single aggregate, and passes the events to a EventListener.
class Replayer implements EventVisitor
{
    public function __construct(EventStoreManagement $eventStore, EventListener $eventListener)
    {
        $this->eventStore = $eventStore;
        $this->eventListener = $eventListener;
    }

    public function doWithEvent(DomainMessage $domainMessage): void
    {
        $this->eventListener->handle($domainMessage);
    }

    public function replayForAggregate(string $aggregateId): void
    {
        $criteria = new Criteria();
        $criteria = $criteria->withAggregateRootIds([$aggregateId]);

        $this->eventStore->visitEvents($criteria, $this);
    }
}

class ExampleEventListener implements EventListener
{
    public function handle(DomainMessage $domainMessage): void
    {
        var_dump($domainMessage->getPayload());
    }
}

$replayer = new Replayer($eventStore, new ExampleEventListener());
$replayer->replayForAggregate($aggregateId);
