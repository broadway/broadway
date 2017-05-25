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
use Broadway\Domain\Metadata;
use Broadway\EventStore\Exception\InvalidIdentifierException;
use Broadway\Serializer\SerializerInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Database;
use Exception;
use MongoClient;
use MongoCollection;
use MongoException;

/**
 * Event store using a relational database as storage.
 *
 * The implementation uses doctrine MongoDB for the communication with the
 * underlying data store.
 */
class MongoDBEventStore implements EventStoreInterface
{
    const FIELD_STREAM_ID   = 'sid';
    const FIELD_COMMIT_ID   = 'cid';
    const FIELD_PLAYHEAD    = 'head';
    const FIELD_PAYLOAD     = 'data';
    const FIELD_METADATA    = 'meta';
    const FIELD_TYPE        = 'type';
    const FIELD_RECORDED_ON = 'time';

    /**
     * @var Collection
     */
    private $eventCollection;

    /**
     * @var Collection
     */
    private $transactionCollection;

    /**
     * @var SerializerInterface
     */
    private $payloadSerializer;

    /**
     * @var SerializerInterface
     */
    private $metadataSerializer;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @param Database               $database
     * @param SerializerInterface    $payloadSerializer
     * @param SerializerInterface    $metadataSerializer
     * @param UuidGeneratorInterface $uuidGenerator
     * @param string                 $eventCollectionName
     * @param string                 $transactCollectionName
     */
    public function __construct(
        Database $database,
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
        UuidGeneratorInterface $uuidGenerator,
        $eventCollectionName,
        $transactCollectionName
    ) {
        $this->payloadSerializer    = $payloadSerializer;
        $this->metadataSerializer   = $metadataSerializer;
        $this->uuidGenerator        = $uuidGenerator;

        $this->eventCollection = $database->selectCollection($eventCollectionName);
        $this->eventCollection->setReadPreference(MongoClient::RP_PRIMARY);

        $this->transactionCollection = $database->selectCollection($transactCollectionName);
        $this->transactionCollection->setReadPreference(MongoClient::RP_PRIMARY);
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $cursor = $this->eventCollection
            ->find(array(
                self::FIELD_STREAM_ID => (string) $id,
                self::FIELD_COMMIT_ID => null,
            ), array(
                '_id' => false,
            ))
            ->sort(array(self::FIELD_PLAYHEAD => MongoCollection::ASCENDING))
        ;

        $events = array();

        foreach ($cursor as $row) {
            $events[] = $this->deserializeEvent($row);
        }

        $cursor = null;

        if (!$events) {
            throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
        }

        return new DomainEventStream($events);
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        $aggregateId = (string) $id;

        $events = $this->extractEvents($eventStream, $aggregateId);

        if (!$events) {
            return;
        }

        try {
            if (1 == count($events)) {
                $this->appendEvent(reset($events));
            } else {
                $this->appendEvents($events);
            }
        } catch (MongoException $exception) {
            throw MongoDBEventStoreException::create($exception);
        } catch (Exception $exception) {
            throw new MongoDBEventStoreException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Configure collection indices. Should be used by migration or by bootstrapping tests.
     */
    public function configureCollection()
    {
        $this->eventCollection->ensureIndex(array(
            self::FIELD_STREAM_ID => MongoCollection::ASCENDING,
            self::FIELD_PLAYHEAD  => MongoCollection::ASCENDING,
        ), array(
            'unique'     => true,
            'background' => true,
        ));
    }

    /**
     * @param DomainEventStreamInterface|DomainMessage[] $eventStream
     * @param string                                     $aggregateId
     *
     * @return array
     */
    private function extractEvents(DomainEventStreamInterface $eventStream, $aggregateId)
    {
        $events = array();

        foreach ($eventStream as $message) {
            $this->assertIdentifier($aggregateId, $message->getId());

            $events[] = $this->serializeEvent($message);
        }

        return $events;
    }

    /**
     * @param string $expected
     * @param string $actual
     */
    private function assertIdentifier($expected, $actual)
    {
        if (0 != strcmp($expected, $actual)) {
            throw new InvalidIdentifierException(sprintf('Expecting %s, got %s identifier.', $expected, $actual));
        }
    }

    /**
     * @param DomainMessage $message
     *
     * @return array
     */
    private function serializeEvent(DomainMessage $message)
    {
        $data = array(
            self::FIELD_STREAM_ID   => (string) $message->getId(),
            self::FIELD_PLAYHEAD    => $message->getPlayhead(),
            self::FIELD_METADATA    => $this->metadataSerializer->serialize($message->getMetadata()),
            self::FIELD_PAYLOAD     => $this->payloadSerializer->serialize($message->getPayload()),
            self::FIELD_RECORDED_ON => $message->getRecordedOn()->toString(),
            self::FIELD_TYPE        => $message->getType(),
        );

        // save space
        if (empty($data[self::FIELD_METADATA])) {
            unset($data[self::FIELD_METADATA]);
        }

        return $data;
    }

    /**
     * @param array $row
     *
     * @return DomainMessage
     */
    private function deserializeEvent(array $row)
    {
        if (empty($row[self::FIELD_METADATA])) {
            $metadata = new Metadata();
        } else {
            $metadata = $this->metadataSerializer->deserialize($row[self::FIELD_METADATA]);
        }

        return new DomainMessage(
            $row[self::FIELD_STREAM_ID],
            $row[self::FIELD_PLAYHEAD],
            $metadata,
            $this->payloadSerializer->deserialize($row[self::FIELD_PAYLOAD]),
            DateTime::fromString($row[self::FIELD_RECORDED_ON])
        );
    }

    /**
     * @param array $event
     */
    private function appendEvent(array $event)
    {
        $this->eventCollection->insert($event, array(
            'safe' => true,
        ));
    }

    /**
     * @param array $events
     *
     * @throws Exception
     */
    private function appendEvents(array $events)
    {
        $commitId = $this->getNewId();

        try {
            $this->beginTransaction($commitId);

            $this->commitEvents($commitId, $events);

            $this->commitTransaction($commitId);
        } catch (Exception $exception) {
            $this->rollbackTransaction($commitId);

            throw $exception;
        }
    }

    /**
     * @param string $commitId
     * @param array  $events
     */
    private function commitEvents($commitId, array $events)
    {
        foreach ($events as &$event) {
            $event[self::FIELD_COMMIT_ID] = $commitId;
        }
        
        unset($event);

        $this->eventCollection->batchInsert($events, array(
            'safe' => true,
        ));
    }

    /**
     * @return string
     */
    private function getNewId()
    {
        return $this->uuidGenerator->generate();
    }

    /**
     * @param string $commitId
     */
    private function beginTransaction($commitId)
    {
        $data = array(
            '_id' => (string) $commitId,
        );

        $this->transactionCollection->insert($data, array(
            'safe' => true,
        ));
    }

    /**
     * @param string $commitId
     */
    private function commitTransaction($commitId)
    {
        $commitId = (string) $commitId;

        $this->eventCollection->update(array(
            self::FIELD_COMMIT_ID => $commitId,
        ), array(
            '$set' => array(self::FIELD_COMMIT_ID => null),
        ), array(
            'multiple' => true,
            'safe'     => true,
        ));

        $this->transactionCollection->remove(array(
            '_id' => $commitId,
        ), array(
            'safe' => true,
        ));
    }

    /**
     * @param string $commitId
     *
     * @throws MongoException
     */
    private function rollbackTransaction($commitId)
    {
        $commitId  = (string) $commitId;
        $exception = null;

        try {
            $this->eventCollection->remove(array(
                self::FIELD_COMMIT_ID => $commitId,
            ), array(
                'safe' => true,
            ));
        } catch (MongoException $e) {
            $exception = $e;
        }

        try {
            $this->transactionCollection->remove(array(
                '_id' => $commitId,
            ), array(
                'safe' => true,
            ));
        } catch (MongoException $e) {
            $exception = $exception ?: $e;
        }

        if (null !== $exception) {
            throw $exception;
        }
    }
}
