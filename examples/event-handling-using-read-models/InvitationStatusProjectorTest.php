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

class InvitationStatusProjectorTest extends Broadway\ReadModel\Testing\ProjectorScenarioTestCase
{

    /**
     * The createProjector function allows you to inject more dependencies into your projector.
     */
    protected function createProjector(Broadway\ReadModel\InMemory\InMemoryRepository $repository): Broadway\ReadModel\Projector
    {
        return new InvitationStatusProjector($repository);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_keeps_track_of_the_status_of_an_invitation_when_someone_is_invited(): void
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_keeps_track_of_the_status_when_an_invitation_is_accepted(): void
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_keeps_track_of_the_status_when_an_invitation_is_declined(): void
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


