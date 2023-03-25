<?php

declare(strict_types=1);

use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;

require_once __DIR__.'/User.php';

class Users extends EventSourcingRepository
{
    public function __construct(EventStore $eventStore, EventBus $eventBus)
    {
        parent::__construct(
            $eventStore,
            $eventBus,
            User::class,
            new Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory()
        );
    }
}
