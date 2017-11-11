<?php

declare(strict_types=1);

namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DomainMessage;
use Webmozart\Assert\Assert;

final class BlacklistConcurrencyConflictResolver implements ConcurrencyConflictResolver
{
    private $conflictingEvents = [];

    /**
     * @param string $eventClass1
     * @param string $eventClass2
     */
    public function registerConflictingEvents(string $eventClass1, string $eventClass2)
    {
        Assert::classExists($eventClass1, $eventClass1.' is not a class');
        Assert::classExists($eventClass2, $eventClass2.' is not a class');

        // bidirectional, unqiue class mapping
        $this->conflictingEvents[$eventClass1][$eventClass2] = true;
        $this->conflictingEvents[$eventClass2][$eventClass1] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function conflictsWith(DomainMessage $event1, DomainMessage $event2): bool
    {
        return isset($this->conflictingEvents[get_class($event1->getPayload())][get_class($event2->getPayload())]);
    }
}
