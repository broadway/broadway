<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * Invitation aggregate root.
 *
 * The aggregate root will guard that the invitation can only be accepted OR
 * declined, but not both.
 */
class Invitation extends Broadway\EventSourcing\EventSourcedAggregateRoot
{
    private $accepted = false;
    private $declined = false;
    private $invitationId;

    /**
     * Factory method to create an invitation.
     */
    public static function invite($invitationId, $name)
    {
        $invitation = new Invitation();

        // After instantiation of the object we apply the "InvitedEvent".
        $invitation->apply(new InvitedEvent($invitationId, $name));

        return $invitation;
    }

    /**
     * Every aggregate root will expose its id.
     *
     * {@inheritDoc}
     */
    public function getAggregateRootId()
    {
        return $this->invitationId;
    }

    /*
     * The two methods below are part of the public API of the aggregate root.
     */

    public function accept()
    {
        // throw if already declined
        if ($this->declined) {
            throw new RuntimeException('Already declined.');
        }

        /* If the invitation is already accepted, nothing happens we do not
         * throw an exception, but also no event is recorded. This is one way of
         * implementing idempotency in your event sourced aggregate roots
         */
        if ($this->accepted) {
            return;
        }

        $this->apply(new AcceptedEvent($this->invitationId));
    }

    public function decline()
    {
        if ($this->accepted) {
            throw new RuntimeException('Already accepted.');
        }

        if ($this->declined) {
            return;
        }

        $this->apply(new DeclinedEvent($this->invitationId));
    }

    /*
     * The methods below are called as the aggregate root is reconstituted from
     * the previously recorded events.
     */

    protected function applyAcceptedEvent(AcceptedEvent $event)
    {
        /* if we encounter an AcceptedEvent we change the internal state of the
         * aggregate root. This happens if the aggregate root gets reconstituted.
         */
        $this->accepted = true;
    }

    protected function applyDeclinedEvent(DeclinedEvent $event)
    {
        $this->declined = true;
    }

    protected function applyInvitedEvent(InvitedEvent $event)
    {
        $this->invitationId = $event->invitationId;
    }
}

/**
 * A repository that will only store and retrieve Invitation aggregate roots.
 *
 * This repository uses the base class provided by the EventSourcing component.
 */
class InvitationRepository extends Broadway\EventSourcing\EventSourcingRepository
{
    public function __construct(Broadway\EventStore\EventStore $eventStore, Broadway\EventHandling\EventBus $eventBus)
    {
        parent::__construct($eventStore, $eventBus, 'Invitation', new Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory());
    }
}

/*
 * When using CQRS with commands, a lot of times you will find that you have a
 * command object and a "dual" event. Mind though that this is not always the
 * case. The following classes show the commands and events for our small
 * domain model.
 */

/* All commands and events below will cary the id of the aggregate root. For
 * our convenience and readability later on we provide base classes that hold
 * this data.
 */
abstract class InvitationCommand
{
    public $invitationId;
    public function __construct($invitationId)
    {
        $this->invitationId = $invitationId;
    }
}
abstract class InvitationEvent
{
    public $invitationId;
    public function __construct($invitationId)
    {
        $this->invitationId = $invitationId;
    }
}

// The "real" commands and events below.
class InviteCommand extends InvitationCommand
{
    public $name;
    public function __construct($invitationId, $name)
    {
        parent::__construct($invitationId);

        $this->name = $name;
    }
}

class InvitedEvent extends InvitationEvent
{
    public $name;
    public function __construct($invitationId, $name)
    {
        parent::__construct($invitationId);

        $this->name = $name;
    }
}

// The meaning from these commands and events can be found in the name :)
class AcceptCommand extends InvitationCommand
{
}
class AcceptedEvent extends InvitationEvent
{
}
class DeclineCommand extends InvitationCommand
{
}
class DeclinedEvent extends InvitationEvent
{
}

/*
 * A command handler will be registered with the command bus and handle the
 * commands that are dispatched. The command handler can be seen as a small
 * layer between your application code and the actual domain code.
 *
 * In the end a command handler listens for commands and translates commands to
 * method calls on the actual aggregate roots.
 */
class InvitationCommandHandler extends Broadway\CommandHandling\SimpleCommandHandler
{
    private $repository;

    public function __construct(Broadway\EventSourcing\EventSourcingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * A new invite aggregate root is created and added to the repository.
     */
    protected function handleInviteCommand(InviteCommand $command)
    {
        $invitation = Invitation::invite($command->invitationId, $command->name);

        $this->repository->save($invitation);
    }

    /**
     * An existing invite is loaded from the repository and the accept() method
     * is called.
     */
    protected function handleAcceptCommand(AcceptCommand $command)
    {
        $invitation = $this->repository->load($command->invitationId);

        $invitation->accept();

        $this->repository->save($invitation);
    }

    protected function handleDeclineCommand(DeclineCommand $command)
    {
        $invitation = $this->repository->load($command->invitationId);

        $invitation->decline();

        $this->repository->save($invitation);
    }
}
