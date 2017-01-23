<?php
namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DomainMessage;
use Webmozart\Assert\Assert;

class BlacklistConcurrencyConflictResolver implements ConcurrencyConflictResolver
{
    private $conflictingEvents = [];

    /**
     * @param string $eventClass1
     * @param string $eventClass2
     */
    public function registerConflictingEvents($eventClass1, $eventClass2)
    {
        Assert::classExists($eventClass1, $eventClass1.' is not a class');
        Assert::classExists($eventClass2, $eventClass2.' is not a class');

        // bidirectional, unqiue class mapping
        $this->conflictingEvents[$eventClass1][$eventClass2] = true;
        $this->conflictingEvents[$eventClass2][$eventClass1] = true;
    }

    public function conflictsWith(DomainMessage $event1, DomainMessage $event2)
    {
        return isset($this->conflictingEvents[get_class($event1->getPayload())][get_class($event2->getPayload())]);
    }
}
