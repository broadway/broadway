<?php

namespace Broadway\EventSourcing\DomainMessageTypeEnrichment;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventSourcing\EventStreamDecoratorInterface;

class DomainMessageTypeEventStreamDecorator implements EventStreamDecoratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function decorateForWrite($aggregateType, $aggregateIdentifier, DomainEventStreamInterface $eventStream)
    {
        $messages = array();

        /** @var DomainMessage $message */
        foreach ($eventStream as $message) {
            $payload = $message->getPayload();
            if (method_exists($payload, 'getType')) {
                $messages[] = $message->andType($payload->getType());
            } else {
                $messages[] = $message;
            }
        }

        return new DomainEventStream($messages);
    }
}
