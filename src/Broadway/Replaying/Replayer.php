<?php

declare(strict_types=1);

namespace MicroModule\Broadway\Replaying;

use MicroModule\Broadway\EventStore\EventVisitor;
use MicroModule\Broadway\EventStore\Management\Criteria;
use MicroModule\Broadway\EventStore\Management\EventStoreManagement;

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
