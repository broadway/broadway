<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Assert\Assertion;
use Broadway\Domain\DomainMessage;

final class WhitelistConcurrencyConflictResolver implements ConcurrencyConflictResolver
{
    private $independentEvents = [];

    public function registerIndependentEvents(string $eventClass1, string $eventClass2): void
    {
        Assertion::classExists($eventClass1, $eventClass1.' is not a class');
        Assertion::classExists($eventClass2, $eventClass2.' is not a class');

        // bidirectional, unique class mapping
        $this->independentEvents[$eventClass1][$eventClass2] = true;
        $this->independentEvents[$eventClass2][$eventClass1] = true;
    }

    public function conflictsWith(DomainMessage $event1, DomainMessage $event2): bool
    {
        return !isset($this->independentEvents[get_class($event1->getPayload())][get_class($event2->getPayload())]);
    }
}
