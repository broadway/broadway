<?php

declare(strict_types=1);

namespace Broadway\Replaying;

use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

final class Replayer
{
    private $eventStore;
    private $eventVisitor;

    public function __construct(
        EventStoreManagement $eventStore,
        EventVisitor $eventVisitor
    ) {
        $this->eventStore = $eventStore;
        $this->eventVisitor = $eventVisitor;
    }

    public function replay(Criteria $criteria)
    {
        $this->eventStore->visitEvents($criteria, $this->eventVisitor);
    }
}
