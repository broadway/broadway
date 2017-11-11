<?php

declare(strict_types=1);

namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

abstract class ConcurrencyConflictResolverTest extends TestCase
{
    protected function createDomainMessage($id, $playhead, $event)
    {
        return new DomainMessage($id, $playhead, new Metadata([]), $event, DateTime::now());
    }
}
