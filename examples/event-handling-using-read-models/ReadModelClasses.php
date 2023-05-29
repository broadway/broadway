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

require_once __DIR__.'/../bootstrap.php';

// We reuse the Invites aggregate and events
require_once __DIR__.'/../event-sourced-domain-with-tests/Invites.php';

use Broadway\ReadModel\Repository;

/*
 * A Projector is an EventListener and can be registered with the EventBus.
 * When an Aggregated is saved to an an EventSourcingRepository its
 * DomainEventStream is stored in the EventStore and the events are published
 * to the EventBus. The EventBus passes the events to all interested
 * EventListeners.
 *
 * Broadway ships with a ReadModel Repository class that can be used to help
 * make and store read models. This example's InvitationStatusProjector shows
 * you how this is done.
 *
 * However, you can easily change how you are going to store and update your
 * read models - simply extend a Projector to get events and do anything you
 * want. InvitationStatusCountProjector shows a simple example of what is
 * possible.
 */
class InvitationStatusProjector extends Broadway\ReadModel\Projector
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function exposeStatusOfInvitation($invitationId): InvitationStatusReadModel
    {
        return $this->loadReadModel($invitationId);
    }

    protected function applyInvitedEvent(InvitedEvent $event)
    {
        $readModel = new InvitationStatusReadModel($event->invitationId);

        $this->repository->save($readModel);
    }

    protected function applyAcceptedEvent(AcceptedEvent $event)
    {
        $readModel = $this->loadReadModel($event->invitationId);
        $readModel->flagAccepted();

        $this->repository->save($readModel);
    }

    protected function applyDeclinedEvent(DeclinedEvent $event)
    {
        $readModel = $this->loadReadModel($event->invitationId);
        $readModel->flagDeclined();

        $this->repository->save($readModel);
    }

    private function loadReadModel($id)
    {
        return $this->repository->find($id);
    }
}

/**
 * InvitationStatusReadModel implements Identifiable (through
 * SerializableReadModel) in order to support the ReadModel Repository. If you
 * are not using this Repository, there is no need to implement Identifiable
 * (nor SerializableReadModel).
 */
class InvitationStatusReadModel implements Broadway\ReadModel\SerializableReadModel
{
    private $invitationId;
    private $isOpen = true;
    private $isAccepted = false;
    private $isDeclined = false;

    public function __construct(string $invitationId)
    {
        $this->invitationId = $invitationId;
    }

    public function getId(): string
    {
        return $this->invitationId;
    }

    public function flagAccepted(): void
    {
        $this->isAccepted = true;
        $this->isOpen = false;
        $this->isDeclined = false;
    }

    public function flagDeclined(): void
    {
        $this->isDeclined = true;
        $this->isOpen = false;
        $this->isAccepted = false;
    }

    public static function deserialize(array $data)
    {
        $readModel = new self($data['invitationId']);

        $readModel->isOpen = $data['isOpen'];
        $readModel->isAccepted = $data['isAccepted'];
        $readModel->isDeclined = $data['isDeclined'];

        return $readModel;
    }

    public function serialize(): array
    {
        return [
            'invitationId' => $this->invitationId,
            'isOpen' => $this->isOpen,
            'isAccepted' => $this->isAccepted,
            'isDeclined' => $this->isDeclined,
        ];
    }

    public function getIsOpen(): bool
    {
        return $this->isOpen;
    }

    public function getIsClosed(): bool
    {
        return !$this->isOpen;
    }

    public function getIsAccepted(): bool
    {
        return $this->isAccepted;
    }

    public function getIsDeclined(): bool
    {
        return $this->isDeclined;
    }
}

/*
 * The following projector keeps track of how many invitations are open,
 * closed, accepted and declined. Note that it does not use a regular
 * ReadModel\Repository or Identifiable ReadModel.
 *
 * The example keeps track of the counts in memory. In a real application some
 * form of storage should be used.
 */
class InvitationStatusCountProjector extends Broadway\ReadModel\Projector
{
    private $repository;

    public function __construct(CounterRepository $repository)
    {
        $this->repository = $repository;
    }

    public function exposeStatusCounts(): Counters
    {
        return $this->getCounters();
    }

    protected function applyInvitedEvent(InvitedEvent $event)
    {
        $counters = $this->getCounters();
        $counters->applyInvited();

        $this->storeCounters($counters);
    }

    protected function applyAcceptedEvent(AcceptedEvent $event)
    {
        $counters = $this->getCounters();
        $counters->applyAccepted();

        $this->storeCounters($counters);
    }

    protected function applyDeclinedEvent(DeclinedEvent $event)
    {
        $counters = $this->getCounters();
        $counters->applyDeclined();

        $this->storeCounters($counters);
    }

    private function storeCounters($counters)
    {
        $this->repository->storeCounters($counters);
    }

    private function getCounters()
    {
        return $this->repository->getCounters();
    }
}

class CounterRepository
{
    private $counters;

    public function storeCounters(Counters $counters): void
    {
        $this->counters = $counters;
    }

    public function getCounters(): Counters
    {
        if (null !== $this->counters) {
            return $this->counters;
        }

        return new Counters();
    }
}

class Counters
{
    public $invitedCounter = 0;
    public $acceptedCounter = 0;
    public $declinedCounter = 0;
    public $openCounter = 0;

    public function applyInvited()
    {
        ++$this->invitedCounter;
        ++$this->openCounter;
    }

    public function applyAccepted()
    {
        ++$this->acceptedCounter;
        --$this->openCounter;
    }

    public function applyDeclined()
    {
        ++$this->declinedCounter;
        --$this->openCounter;
    }
}
