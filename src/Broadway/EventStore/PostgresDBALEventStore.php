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

use Broadway\Domain\DomainMessage;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

/**
 * Event store using a PostgreSQL database as storage.
 *
 * The implementation uses doctrine DBAL for the communication with the
 * underlying data store, and has the SQL definitions tailored specifically to
 * deal with PostgreSQL's default of forcing lowercase for all column names.
 */
class PostgresDBALEventStore extends DBALEventStore
{
    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->tableName);

        $table->addColumn("\"id\"", 'integer', array('autoincrement' => true));
        $table->addColumn("\"uuid\"", 'guid', array('length' => 36));
        $table->addColumn("\"playhead\"", 'integer', array('unsigned' => true));
        $table->addColumn("\"payload\"", 'text');
        $table->addColumn("\"metadata\"", 'text');
        $table->addColumn("\"recordedOn\"", 'string', array('length' => 32));
        $table->addColumn("\"type\"", 'text');

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('uuid', 'playhead'));

        return $table;
    }

    protected function insertMessage(Connection $connection, DomainMessage $domainMessage)
    {
        $data = array(
            '"uuid"'       => $domainMessage->getId(),
            '"playhead"'   => $domainMessage->getPlayhead(),
            '"metadata"'   => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            '"payload"'    => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),
            '"recordedOn"' => $domainMessage->getRecordedOn()->toString(),
            '"type"'       => $domainMessage->getType(),
        );

        $connection->insert($this->tableName, $data);
    }

    protected function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $query = 'SELECT "uuid", "playhead", "metadata", "payload", "recordedOn"
                FROM ' . $this->tableName . '
                WHERE "uuid" = :uuid
                ORDER BY "playhead" ASC';
            $this->loadStatement = $this->connection->prepare($query);
        }

        return $this->loadStatement;
    }
}
