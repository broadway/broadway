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

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\Dbal\DBALEventStore;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Doctrine\DBAL\DriverManager;

require __DIR__.'/query-memory-events-with-criteria.php';

if (!class_exists(DBALEventStore::class)) {
    $logger->error('The query-dbal-events example is only runnable when you have DBALEventStore available');

    return 1;
}

$serializer = new SimpleInterfaceSerializer();

$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
$schemaManager = $connection->getSchemaManager();

$schema = $schemaManager->createSchema();

$store = new DBALEventStore($connection, $serializer, $serializer, 'events', false);
$schemaManager->createTable($store->configureTable($schema));

$store->append($staleInvited->getAggregateRootId(), $staleInvitationEvents);
$store->append($acceptedInvite->getAggregateRootId(), $acceptedInviteEvents);
$store->append($declinedInvite->getAggregateRootId(), $declinedInviteEvents);

// That other example was with the InMemoryStore, but you can use the same idea
// with more complex SQL queries that are not supported with the Criteria object

$sql = "SELECT a.*
    FROM events a
    LEFT JOIN events b ON a.uuid = b.uuid
    AND b.type IN ('AcceptedEvent', 'DeclinedEvent')
    WHERE a.`type` = 'InvitedEvent' AND b.uuid IS NULL;";

$stmt = $connection->executeQuery($sql);
$stmt->execute();

$staleInvites = [];
while ($row = $stmt->fetch()) {
    // Rebuilding of the DomainMessage
    // the specifics depend on the event-store implementation
    // this example is based on the broadway/event-store-dbal implementation
    $domainMessage = new DomainMessage(
        $row['uuid'],
        (int) $row['playhead'],
        $serializer->deserialize(json_decode($row['metadata'], true)),
        $serializer->deserialize(json_decode($row['payload'], true)),
        DateTime::fromString($row['recorded_on'])
    );
    $staleInvites[$row['uuid']] = $domainMessage;
}

foreach ($staleInvites as $inviteMessage) {
    $logger->info('Stale invite (from sql via domainmessage): '.$inviteMessage->getId());
}
