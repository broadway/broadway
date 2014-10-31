<?php

namespace Broadway\EventStore;

use Broadway\Domain\DomainMessageInterface;

interface EventVisitorInterface
{
    /**
     * Called for each event loaded from the event store.
     *
     * @param DomainMessageInterface $domainMessage to be loaded
     * @return void
     */
    public function doWithEvent(DomainMessageInterface $domainMessage);
}
