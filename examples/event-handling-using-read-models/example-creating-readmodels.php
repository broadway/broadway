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

$invitationStatusProjector = new InvitationStatusProjector(new Broadway\ReadModel\InMemory\InMemoryRepository());
$invitationStatusCountProjector = new InvitationStatusCountProjector(new CounterRepository());

$eventStore = new Broadway\EventStore\InMemoryEventStore();

// We subscribe the projectors to the event bus
$eventBus = new Broadway\EventHandling\SimpleEventBus();
$eventBus->subscribe($invitationStatusProjector);
$eventBus->subscribe($invitationStatusCountProjector);

$commandBus = new Broadway\CommandHandling\SimpleCommandBus();
// The InvitationRepository gets both the event store and event bus. When
// saving an aggregate, events are persisted in the event store, and all
// subscribers to the event bus get notified.
$commandBus->subscribe(new InvitationCommandHandler(new InvitationRepository($eventStore, $eventBus)));

// We dispatch the commands to the command bus. The command handler receives
// the commands and stores them to the InvitationRepository. The
// InvitationRepository makes sure the events are persisted in the event store,
// and passes the events to the event bus. The event bus makes sure our
// projectors receives the events, allowing our read models to be updated.
$commandBus->dispatch(new InviteCommand('invitationId', 'John Doe'));
$commandBus->dispatch(new InviteCommand('anotherInvitation', 'Jane Doe'));
$commandBus->dispatch(new InviteCommand('a third invitation', '<insert name here>'));
$commandBus->dispatch(new AcceptCommand('invitationId'));
$commandBus->dispatch(new DeclineCommand('a third invitation'));

var_dump($invitationStatusProjector->exposeStatusOfInvitation('invitationId'));
var_dump($invitationStatusCountProjector->exposeStatusCounts());
