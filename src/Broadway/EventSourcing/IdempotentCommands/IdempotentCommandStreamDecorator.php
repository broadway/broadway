<?php
namespace Broadway\EventSourcing\IdempotentCommands;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\EventStoreInterface;

class IdempotentCommandStreamDecorator implements EventStreamDecoratorInterface
{
    /** @var EventStoreInterface */
    private $eventStore;

    /** @var string */
    private $metaDataKey;

    /** @var string */
    private $commandId;

    /**
     * IdempotentCommandStreamDecorator constructor.
     *
     * @param EventStoreInterface $eventStore
     * @param string              $commandId
     * @param string              $metaDataKey
     */
    public function __construct(EventStoreInterface $eventStore, $commandId, $metaDataKey = 'command-id')
    {
        $this->eventStore  = $eventStore;
        $this->metaDataKey = $metaDataKey;
        $this->commandId   = $commandId;
    }

    /**
     * @inheritDoc
     */
    public function decorateForWrite($aggregateType, $aggregateIdentifier, DomainEventStreamInterface $eventStream)
    {
        if ($this->existsEventWithCommandId($this->commandId, $aggregateIdentifier)) {
            throw new DuplicateCommandException('Command #'.$this->commandId.' has already been executed');
        }

        $domainMessagesWithMetaData = [];
        /** @var DomainMessage $domainMessage */
        foreach ($eventStream as $domainMessage) {
            $domainMessagesWithMetaData[] = $domainMessage->andMetadata(
                Metadata::kv($this->metaDataKey, $this->commandId));
        }

        return new DomainEventStream($domainMessagesWithMetaData);
    }

    /**
     * @param DomainMessage $domainMessage
     *
     * @return string|null
     */
    private function getCommandId(DomainMessage $domainMessage)
    {
        $metaData = $domainMessage->getMetadata()
                                  ->serialize();

        // @see http://stackoverflow.com/questions/4260086/php-how-to-use-array-filter-to-filter-array-keys
        $commandId = array_intersect_key($metaData, array_flip([$this->metaDataKey]));

        if (!$commandId) {
            return null;
        }

        return reset($commandId);
    }

    /**
     * @param string $commandId
     * @param string $aggregateId
     *
     * @return bool
     */
    private function existsEventWithCommandId($commandId, $aggregateId)
    {
        /** @var DomainMessage $domainMessage */
        foreach ($this->eventStore->load($aggregateId) as $domainMessage) {
            $committedCommandId = $this->getCommandId($domainMessage);
            if ($committedCommandId == $commandId) {
                return true;
            }
        }

        return false;
    }
}

