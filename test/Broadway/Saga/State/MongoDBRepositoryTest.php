<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\State;

use MongoDB\Client;

/**
 * @group mongo
 * @requires extension mongo
 */
class MongoDBRepositoryTest extends AbstractRepositoryTest
{
    protected static $dbName = 'doctrine_mongodb';
    protected $db;

    protected function createRepository()
    {
        $client = new Client(getenv('MONGODB_DSN') ?: 'mongodb://localhost:27017');

        $this->db = $client->selectDatabase(self::$dbName);
        $this->db->dropCollection('test');
        $this->db->createCollection('test');

        return new MongoDBRepository($this->db->selectCollection('test'));
    }
}
