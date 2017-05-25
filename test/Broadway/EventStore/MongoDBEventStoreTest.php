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
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;

/**
 * @group mongo
 */
class MongoDBEventStoreTest extends EventStoreTest
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventStore = $this->createEventStore();
        $this->eventStore->configureCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $database = $this->connection->selectDatabase(MONGODB_DATABASE);

        foreach ($database->listCollections() as $collection) {
            $collection->drop();
        }

        $this->connection->close();
        $this->connection = null;
    }

    /**
     * @return MongoDBEventStore
     */
    protected function createEventStore()
    {
        $config = new Configuration();
        $config->setLoggerCallable(function ($msg) {});

        $this->connection = new Connection(MONGODB_SERVER, array(), $config);

        $database = $this->connection->selectDatabase(MONGODB_DATABASE);

        return new MongoDBEventStore(
            $database,
            new SimpleInterfaceSerializer(),
            new SimpleInterfaceSerializer(),
            new Version4Generator(),
            'event_store',
            'event_transaction'
        );
    }
}
