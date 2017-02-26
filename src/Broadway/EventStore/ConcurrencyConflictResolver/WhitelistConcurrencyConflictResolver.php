<?php
namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DomainMessage;
use Webmozart\Assert\Assert;

class WhitelistConcurrencyConflictResolver implements ConcurrencyConflictResolver
{
    private $independentEvents = [];

    /**
     * @param string $eventClass1
     * @param string $eventClass2
     */
    public function registerIndependentEvents($eventClass1, $eventClass2)
    {
        Assert::classExists($eventClass1, $eventClass1.' is not a class');
        Assert::classExists($eventClass2, $eventClass2.' is not a class');

        // bidirectional, unique class mapping
        $this->independentEvents[$eventClass1][$eventClass2] = true;
        $this->independentEvents[$eventClass2][$eventClass1] = true;
    }

    public function conflictsWith(DomainMessage $event1, DomainMessage $event2)
    {
        return !isset($this->independentEvents[get_class($event1->getPayload())][get_class($event2->getPayload())]);
    }
}
