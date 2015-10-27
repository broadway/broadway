<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Broadway\Serializer\SimpleInterfaceSerializer;
use Doctrine\DBAL\DriverManager;

/**
 * @requires extension pdo_sqlite
 */
class DBALEventStoreTest extends EventStoreTest
{
    public function setUp()
    {
        $connection       = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $schemaManager    = $connection->getSchemaManager();
        $schema           = $schemaManager->createSchema();
        $this->eventStore = new DBALEventStore($connection, new SimpleInterfaceSerializer(), new SimpleInterfaceSerializer(), 'events');

        $table = $this->eventStore->configureSchema($schema);
        $schemaManager->createTable($table);
    }
}
