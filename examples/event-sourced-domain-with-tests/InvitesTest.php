<?php

require_once __DIR__ . '/Invites.php';

/**.
 *
 * An aggregate root scenario consists of three steps:
 *
 * - First, the scenario is setup with a history of events that already took place.
 * - Second, an action is taken on the aggregate.
 * - Third, the outcome is asserted. This can either be 1) some events are
 *   recorded, or 2) an exception is thrown.
 */
class InvitationTest extends Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase
{
    private $generator;

    public function setUp()
    {
        parent::setUp();
        $this->generator = new Broadway\UuidGenerator\Rfc4122\Version4Generator();
    }

    protected function getAggregateRootClass()
    {
        return Invitation::class;
    }

    /**
     * @test
     */
    public function it_can_invite_someone()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->when(function () use ($id) {
                return Invitation::invite($id, 'asm89');
            })
            ->then([new InvitedEvent($id, 'asm89')]);
    }

    /**
     * @test
     */
    public function new_invites_can_be_accepted()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89')])
            ->when(function ($invite) {
                $invite->accept();
            })
            ->then([new AcceptedEvent($id)]);
    }

    /**
     * @test
     */
    public function accepting_an_accepted_invite_yields_no_change()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89'), new AcceptedEvent($id)])
            ->when(function ($aggregate) {
                $aggregate->accept();
            })
            ->then([]);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Already accepted.
     */
    public function an_accepted_invite_cannot_be_declined()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89'), new AcceptedEvent($id)])
            ->when(function ($invite) {
                $invite->decline();
            });
    }

    /**
     * @test
     */
    public function new_invites_can_be_declined()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89')])
            ->when(function ($invite) {
                $invite->decline();
            })
            ->then([new DeclinedEvent($id)]);
    }

    /**
     * @test
     */
    public function declining_a_declined_invite_yields_no_change()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89'), new DeclinedEvent($id)])
            ->when(function ($invite) {
                $invite->decline();
            })
            ->then([]);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Already declined.
     */
    public function a_declined_invite_cannot_be_accepted()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new InvitedEvent($id, 'asm89'), new DeclinedEvent($id)])
            ->when(function ($invite) {
                $invite->accept();
            });
    }
}
