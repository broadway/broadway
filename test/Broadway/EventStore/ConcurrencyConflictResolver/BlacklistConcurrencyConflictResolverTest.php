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

class BlacklistConcurrencyConflictResolverTest extends ConcurrencyConflictResolverTest
{
    /** @var BlacklistConcurrencyConflictResolver */
    private $conflictResolver;

    protected function setUp(): void
    {
        $this->conflictResolver = new BlacklistConcurrencyConflictResolver();
    }

    /** @test */
    public function events_never_conflict_if_no_conflicting_events_are_registered()
    {
        $event = $this->createDomainMessage(1, 0, new Event());
        $this->assertFalse($this->conflictResolver->conflictsWith($event, $event));
    }

    /** @test */
    public function independent_events_do_not_conflict()
    {
        $event = $this->createDomainMessage(1, 0, new Event());
        $otherEvent = $this->createDomainMessage(1, 0, new OtherEvent());
        $this->conflictResolver->registerConflictingEvents(Event::class, Event::class);
        $this->assertTrue($this->conflictResolver->conflictsWith($event, $event));
        $this->assertFalse($this->conflictResolver->conflictsWith($event, $otherEvent));

        $this->conflictResolver->registerConflictingEvents(Event::class, OtherEvent::class);
        $this->assertTrue($this->conflictResolver->conflictsWith($otherEvent, $event));
        $this->assertTrue($this->conflictResolver->conflictsWith($event, $otherEvent));
    }
}
