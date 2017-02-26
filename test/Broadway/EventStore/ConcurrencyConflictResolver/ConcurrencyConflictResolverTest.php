<?php
namespace Broadway\EventStore\ConcurrencyConflictResolver;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;

abstract class ConcurrencyConflictResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function createDomainMessage($id, $playhead, $event)
    {
        return new DomainMessage($id, $playhead, new Metadata([]), $event, DateTime::now());
    }
}
