<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore\Management;

use Broadway\EventStore\DBALEventStore;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Version;

/**
 * @requires extension pdo_sqlite
 */
class BinaryDBALEventStoreManagementTest extends DBALEventStoreManagementTest
{
    /** @var \Doctrine\DBAL\Schema\Table  */
    protected $table;

    public function createEventStore()
    {
        if (Version::compare('2.5.0') >= 0) {
            $this->markTestSkipped('Binary type is only available for Doctrine >= v2.5');
        }

        $connection       = DriverManager::getConnection(array('driver' => 'pdo_sqlite', 'memory' => true));
        $schemaManager    = $connection->getSchemaManager();
        $schema           = $schemaManager->createSchema();
        $eventStore = new DBALEventStore($connection, new SimpleInterfaceSerializer(), new SimpleInterfaceSerializer(), 'events', true);

        $this->table = $eventStore->configureSchema($schema);

        $schemaManager->createTable($this->table);

        return $eventStore;
    }

}
