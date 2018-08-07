<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once __DIR__.'/ReadModelClasses.php';

class InvitationStatusProjectorTest extends Broadway\ReadModel\Testing\ProjectorScenarioTestCase
{
    /**
     * The createProjector function allows you to inject more dependencies into your projector.
     */
    protected function createProjector(Broadway\ReadModel\InMemory\InMemoryRepository $repository): Broadway\ReadModel\Projector
    {
        $this->repository = $repository;

        return new InvitationStatusProjector($repository);
    }

    /**
     * @test
     */
    public function it_keeps_track_of_the_status_of_an_invitation_when_someone_is_invited()
    {
        $invitationId = '1337';
        $expectedReadModel = new InvitationStatusReadModel($invitationId);
        $this->assertEquals($expectedReadModel->getId(), $invitationId);
        $this->assertTrue($expectedReadModel->getIsOpen());
        $this->assertFalse($expectedReadModel->getIsClosed());
        $this->assertFalse($expectedReadModel->getIsAccepted());
        $this->assertFalse($expectedReadModel->getIsDeclined());

        $this->scenario
            ->given([])
            ->when(new InvitedEvent($invitationId, 'fritsjanb'))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_keeps_track_of_the_status_when_an_invitation_is_accepted()
    {
        $invitationId = '1337';

        $expectedReadModel = new InvitationStatusReadModel($invitationId);
        $expectedReadModel->flagAccepted();

        $this->assertEquals($expectedReadModel->getId(), $invitationId);
        $this->assertFalse($expectedReadModel->getIsOpen());
        $this->assertTrue($expectedReadModel->getIsClosed());
        $this->assertTrue($expectedReadModel->getIsAccepted());
        $this->assertFalse($expectedReadModel->getIsDeclined());

        $this->scenario
            ->given([new InvitedEvent($invitationId, 'fritsjanb')])
            ->when(new AcceptedEvent($invitationId))
            ->then([$expectedReadModel]);
    }

    /**
     * @test
     */
    public function it_keeps_track_of_the_status_when_an_invitation_is_declined()
    {
        $invitationId = '1337';

        $expectedReadModel = new InvitationStatusReadModel($invitationId);
        $expectedReadModel->flagDeclined();

        $this->assertEquals($expectedReadModel->getId(), $invitationId);
        $this->assertFalse($expectedReadModel->getIsOpen());
        $this->assertTrue($expectedReadModel->getIsClosed());
        $this->assertFalse($expectedReadModel->getIsAccepted());
        $this->assertTrue($expectedReadModel->getIsDeclined());

        $this->scenario
            ->given([new InvitedEvent($invitationId, 'fritsjanb')])
            ->when(new DeclinedEvent($invitationId))
            ->then([$expectedReadModel]);
    }
}

class InvitationStatusCountProjectorTest extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_keeps_track_of_the_status_counts_of_all_invitations()
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
