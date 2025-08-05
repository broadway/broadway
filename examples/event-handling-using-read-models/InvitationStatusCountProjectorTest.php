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

require_once __DIR__.'/ReadModelClasses.php';

class InvitationStatusCountProjectorTest extends PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_keeps_track_of_the_status_counts_of_all_invitations(): void
    {
        $projector = new InvitationStatusCountProjector(new CounterRepository());

        $id1 = 'id-1';
        $id2 = 'id-2';
        $id3 = 'id-3';
        $projector->handle($this->createDomainMessageForEvent(new InvitedEvent($id1, 'fritsjanb'), 0));
        $projector->handle($this->createDomainMessageForEvent(new InvitedEvent($id2, 'John Doe'), 0));
        $projector->handle($this->createDomainMessageForEvent(new AcceptedEvent($id2), 1));
        $projector->handle($this->createDomainMessageForEvent(new InvitedEvent($id3, 'Jane Doe'), 0));
        $projector->handle($this->createDomainMessageForEvent(new DeclinedEvent($id3), 1));

        $expectedCounters = new Counters();
        $expectedCounters->invitedCounter = 3;
        $expectedCounters->openCounter = 1;
        $expectedCounters->acceptedCounter = 1;
        $expectedCounters->declinedCounter = 1;

        $this->assertEquals($projector->exposeStatusCounts(), $expectedCounters);
    }

    private function createDomainMessageForEvent(InvitationEvent $event, $playhead): Broadway\Domain\DomainMessage
    {
        $occurredOn = Broadway\Domain\DateTime::now();

        return new Broadway\Domain\DomainMessage($event->invitationId, $playhead, new Broadway\Domain\Metadata([]), $event, $occurredOn);
    }
}
