<?php
namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DomainMessage;

interface ConcurrencyConflictResolver
{
    public function conflictsWith(DomainMessage $event1, DomainMessage $event2);
}
