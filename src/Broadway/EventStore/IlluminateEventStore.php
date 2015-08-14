<?php namespace App\Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\DateTime;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\EventStore\IlluminateEventStoreException;
use Broadway\Serializer\SerializerInterface;
use Illuminate\Contracts\Container\Container;

/**
 * Class IlluminateEventStore
 *
 * Create a broadway.php config file in your config directory to change the table name
 *
 * @author  Dennis Schepers
 * @package App\Broadway\EventStore
 */
class IlluminateEventStore implements EventStoreInterface {

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var \Broadway\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Construct the dependancies
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Container $container,
        SerializerInterface $serializer
    ) {
        $this->databaseManager = $container->make('db');
        $this->config = $container->make('config');
        $this->table = $this->config->get('broadway.table','event');
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $results = $this->databaseManager->connection()->table($this->table)->where('uuid', $id)->get();

        if(!$results) {
            throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
        }
        $events = [];
        foreach ($results as $row) {
            $events[] = $this->deserializeEvent($row);
        }
        return new DomainEventStream($events);
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        $connection = $this->databaseManager->connection();

        try {
            $connection->beginTransaction();
            foreach ($eventStream as $domainMessage) {
                /* @var $domainMessage DomainMessage */
                $connection->table($this->table)->insertGetId([
                        'uuid'        => (string) $domainMessage->getId(),
                        'playhead'    => $domainMessage->getPlayhead(),
                        'metadata'    => json_encode($this->serializer->serialize($domainMessage->getMetadata())),
                        'payload'     => json_encode($this->serializer->serialize($domainMessage->getPayload())),
                        'recorded_on' => $domainMessage->getRecordedOn()->toString(),
                        'type'        => $domainMessage->getType(),
                    ]);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new IlluminateEventStoreException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Deserialize a result row to a DomainMessage
     *
     * @author Dennis Schepers
     *
     * @param $row
     *
     * @return \Broadway\Domain\DomainMessage
     */
    protected function deserializeEvent($row)
    {
        return new DomainMessage(
            $row->uuid,
            $row->playhead,
            $this->serializer->deserialize(json_decode($row->metadata, true)),
            $this->serializer->deserialize(json_decode($row->payload, true)),
            DateTime::fromString($row->recorded_on)
        );
    }
}