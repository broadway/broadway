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

use MicroModule\Broadway\Domain\DomainEventStream;
use MicroModule\Broadway\EventStore\CallableEventVisitor;
use MicroModule\Broadway\EventStore\InMemoryEventStore;
use MicroModule\Broadway\EventStore\Management\Criteria;

require __DIR__.'/../event-sourced-domain-with-tests/Invites.php';

$logger = new StdoutLogger();
$store = new InMemoryEventStore();

// Prepare some invites
$staleInvited = Invitation::invite('061e5360-ff48-4468-a672-e49ed77e0fc2', 'Barry');
$acceptedInvite = Invitation::invite('590ae831-b854-40e6-bcc2-ca7d9d552421', 'Mary');
$acceptedInvite->accept();
$declinedInvite = Invitation::invite('caae5d47-5ee7-4821-9f21-a2b65b373438', 'Larry');
$declinedInvite->decline();

// Store the events
$staleInvitationEvents = $staleInvited->getUncommittedEvents();
$acceptedInviteEvents = $acceptedInvite->getUncommittedEvents();
$declinedInviteEvents = $declinedInvite->getUncommittedEvents();
$store->append($staleInvited->getAggregateRootId(), $staleInvitationEvents);
$store->append($acceptedInvite->getAggregateRootId(), $acceptedInviteEvents);
$store->append($declinedInvite->getAggregateRootId(), $declinedInviteEvents);

$allMessages = new DomainEventStream(array_merge(
    $staleInvitationEvents->getIterator()->getArrayCopy(),
    $acceptedInviteEvents->getIterator()->getArrayCopy(),
    $declinedInviteEvents->getIterator()->getArrayCopy()
));

// Create the criteria to retrieve AcceptedEvent events
$acceptedInvitesCriteria = Criteria::create()->withEventTypes([strtr(AcceptedEvent::class, '\\', '.')]);

// Query the event store with the criteria and fill an array with ids of accepted invites
$acceptedEventsReadModel = [];
$store->visitEvents($acceptedInvitesCriteria, new CallableEventVisitor(function ($domainMessage) use (&$acceptedEventsReadModel) {
    $acceptedEventsReadModel[] = ['inviteId' => $domainMessage->getPayload()->invitationId];
}));

$logger->info('Accepted invites', $acceptedEventsReadModel);

// For more complex cases you would need more complex queries.
// The Criteria will only deal with simple use cases.
// If you need more complex queries you could do something like this

$staleInvites = [];
foreach ($allMessages as $domainMessage) {
    switch ($domainMessage->getType()) {
        case 'InvitedEvent':
            $staleInvites[(string) $domainMessage->getId()] = $domainMessage;
            break;
        case 'AcceptedEvent':
        case 'DeclinedEvent':
            unset($staleInvites[(string) $domainMessage->getId()]);
            break;
    }
}

$logger->info('Stale invites', array_keys($staleInvites));

foreach (array_keys($staleInvites) as $inviteId) {
    $invite = $store->load($inviteId);
    // Example: if we would want to expire invites that haven't been accepted or declined yet
    // $invite->expire();
}
