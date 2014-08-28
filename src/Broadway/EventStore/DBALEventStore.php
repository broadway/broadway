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

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\StreamsDomainEvents;
use Broadway\Domain\DomainMessage;
use Broadway\Serializer\SerializesObjects;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;

/**
 * Event store using a relational database as storage.
 *
 * The implementation uses doctrine DBAL for the communication with the
 * underlying data store.
 */
class DBALEventStore implements EventStore
{
    private $connection;

    private $payloadSerializer;

    private $metadataSerializer;

    private $loadStatement = null;

    private $tableName;

    /**
     * @param string $tableName
     */
    public function __construct(
        Connection $connection,
        SerializesObjects $payloadSerializer,
        SerializesObjects $metadataSerializer,
        $tableName
    ) {
        $this->connection         = $connection;
        $this->payloadSerializer  = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName          = $tableName;
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $statement = $this->prepareLoadStatement();
        $statement->bindValue('uuid', $id, 'guid');
        $statement->execute();

        $events = array();
        while ($row = $statement->fetch()) {
            $events[] = $this->deserializeEvent($row);
        }

        if (empty($events)) {
            throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
        }

        return new DomainEventStream($events);
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, StreamsDomainEvents $eventStream)
    {
        $this->connection->beginTransaction();

        try {
            foreach ($eventStream as $domainMessage) {
                $this->insertMessage($this->connection, $domainMessage);
            }

            $this->connection->commit();
        } catch (DBALException $exception) {
            $this->connection->rollback();

            throw DBALEventStoreException::create($exception);
        }
    }

    private function insertMessage(Connection $connection, DomainMessage $domainMessage)
    {
        $data = array(
            'uuid'       => $domainMessage->getId(),
            'playhead'   => $domainMessage->getPlayhead(),
            'metadata'   => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            'payload'    => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),
            'recordedOn' => $domainMessage->getRecordedOn()->toString(),
            'type'       => $domainMessage->getType(),
        );

        $connection->insert($this->tableName, $data);
    }

    /**
     * @return Doctrine\DBAL\Schema\Table|null
     */
    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return $this->configureTable();
    }

    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->tableName);

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('uuid', 'guid', array('length' => 36));
        $table->addColumn('playhead', 'integer', array('unsigned' => true));
        $table->addColumn('payload', 'text');
        $table->addColumn('metadata', 'text');
        $table->addColumn('recordedOn', 'string', array('length' => 32));
        $table->addColumn('type', 'text');

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('uuid', 'playhead'));

        return $table;
    }

    private function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $query = 'SELECT uuid, playhead, metadata, payload, recordedOn
                FROM ' . $this->tableName . '
                WHERE uuid = :uuid
                ORDER BY playhead ASC';
            $this->loadStatement = $this->connection->prepare($query);
        }

        return $this->loadStatement;
    }

    private function deserializeEvent($row)
    {
        return new DomainMessage(
            $row['uuid'],
            $row['playhead'],
            $this->metadataSerializer->deserialize(json_decode($row['metadata'], true)),
            $this->payloadSerializer->deserialize(json_decode($row['payload'], true)),
            DateTime::fromString($row['recordedOn'])
        );
    }
}
