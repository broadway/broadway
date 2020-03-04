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

namespace Broadway\Domain;

use PHPUnit\Framework\TestCase;

class DomainEventStreamTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_events_when_traversing()
    {
        $expectedEvents = ['event1', 'event2', 'event42'];
        $domainEventStream = new DomainEventStream($expectedEvents);

        $events = [];
        foreach ($domainEventStream as $event) {
            $events[] = $event;
        }

        $this->assertEquals($expectedEvents, $events);
    }
}
