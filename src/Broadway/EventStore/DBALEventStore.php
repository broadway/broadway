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
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\Exception\InvalidIdentifierException;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Version;
use Rhumsaa\Uuid\Uuid;

/**
 * Event store using a relational database as storage.
 *
 * The implementation uses doctrine DBAL for the communication with the
 * underlying data store.
 */
class DBALEventStore implements EventStoreInterface
{
    private $connection;

    private $payloadSerializer;

    private $metadataSerializer;

    private $loadStatement = null;

    private $tableName;

    private $useBinary;

    /**
     * @param string $tableName
     */
    public function __construct(
        Connection $connection,
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
        $tableName,
        $useBinary = false
    ) {
        $this->connection         = $connection;
        $this->payloadSerializer  = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName          = $tableName;
        $this->useBinary          = (bool) $useBinary;

        if ($this->useBinary && Version::compare('2.5.0') >= 0) {
            throw new \InvalidArgumentException(
                'The Binary storage is only available with Doctrine DBAL >= 2.5.0'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $statement = $this->prepareLoadStatement();
        $statement->bindValue(1, $this->convertIdentifierToStorageValue($id));
        $statement->execute();

        $events = array();
        while ($row = $statement->fetch()) {
            if ($this->useBinary) {
                $row['uuid'] = $this->convertStorageValueToIdentifier($row['uuid']);
            }
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
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        // noop to ensure that an error will be thrown early if the ID
        // is not something that can be converted to a string. If we
        // let this move on without doing this DBAL will eventually
        // give us a hard time but the true reason for the problem
        // will be obfuscated.
        $id = (string) $id;

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
            'uuid'        => $this->convertIdentifierToStorageValue((string) $domainMessage->getId()),
            'playhead'    => $domainMessage->getPlayhead(),
            'metadata'    => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            'payload'     => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),
            'recorded_on' => $domainMessage->getRecordedOn()->toString(),
            'type'        => $domainMessage->getType(),
        );

        $connection->insert($this->tableName, $data);
    }

    /**
     * @return \Doctrine\DBAL\Schema\Table|null
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

        $uuidColumnDefinition = array(
            'type'   => 'guid',
            'params' => array(
                'length' => 36,
            ),
        );

        if ($this->useBinary) {
            $uuidColumnDefinition['type']   = 'binary';
            $uuidColumnDefinition['params'] = array(
                'length' => 16,
                'fixed'  => true,
            );
        }

        $table = $schema->createTable($this->tableName);

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('uuid', $uuidColumnDefinition['type'], $uuidColumnDefinition['params']);
        $table->addColumn('playhead', 'integer', array('unsigned' => true));
        $table->addColumn('payload', 'text');
        $table->addColumn('metadata', 'text');
        $table->addColumn('recorded_on', 'string', array('length' => 32));
        $table->addColumn('type', 'text');

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('uuid', 'playhead'));

        return $table;
    }

    private function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $query = 'SELECT uuid, playhead, metadata, payload, recorded_on
                FROM ' . $this->tableName . '
                WHERE uuid = ?
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
            DateTime::fromString($row['recorded_on'])
        );
    }

    private function convertIdentifierToStorageValue($id)
    {
        if ($this->useBinary) {
            try {
                return Uuid::fromString($id)->getBytes();
            } catch (\Exception $e) {
                throw new InvalidIdentifierException(
                    'Only valid UUIDs are allowed to by used with the binary storage mode.'
                );
            }
        }

        return $id;
    }

    private function convertStorageValueToIdentifier($id)
    {
        if ($this->useBinary) {
            try {
                return Uuid::fromBytes($id)->toString();
            } catch (\Exception $e) {
                throw new InvalidIdentifierException(
                    'Could not convert binary storage value to UUID.'
                );
            }
        }

        return $id;
    }
}
