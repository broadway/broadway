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
use Broadway\EventStore\DBAL\Criteria\ParameterRegistry;
use Broadway\EventStore\Management\CriteriaInterface;
use Broadway\EventStore\Management\EventStoreManagementInterface;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;

/**
 * Event store using a relational database as storage.
 *
 * The implementation uses doctrine DBAL for the communication with the
 * underlying data store.
 */
class DBALEventStore implements EventStoreInterface, EventStoreManagementInterface
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
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
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
        $statement->bindValue('uuid', (string) $id, 'guid');
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
            'uuid'        => $domainMessage->getId(),
            'playhead'    => $domainMessage->getPlayhead(),
            'metadata'    => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            'payload'     => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),
            'recorded_on' => $domainMessage->getRecordedOn()->toString(),
            'type'        => $domainMessage->getType(),
        );

        $connection->insert($this->tableName, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function visitEvents(EventVisitorInterface $visitor, CriteriaInterface $criteria = null)
    {
        if ($criteria) {
            $whereClause = '';
            $parameterRegistry = new ParameterRegistry();
            $criteria->parse('', $whereClause, $parameterRegistry);
            $parameters = $parameterRegistry->getParameters();
        } else {
            $whereClause = null;
            $parameters = array();
        }
        $this->doVisitEvents($visitor, $whereClause, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function newCriteriaBuilder()
    {
        return new DBALCriteriaBuilder();
    }

    private function doVisitEvents(EventVisitorInterface $visitor, $whereClause, array $parameters)
    {
        $connection = $this->connection;
        $tableName = $this->tableName;
        $query = sprintf(
            'SELECT uuid, playhead, metadata, payload, recorded_on FROM %s WHERE %s ORDER BY recorded_on ASC, playhead ASC',
            $tableName,
            $whereClause ?: '1'
        );

        $statement = $connection->prepare($query);
        $statement->execute($parameters);
        while ($row = $statement->fetch()) {
            $visitor->doWithEvent($this->deserializeEvent($row));
        }
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
            DateTime::fromString($row['recorded_on'])
        );
    }
}
