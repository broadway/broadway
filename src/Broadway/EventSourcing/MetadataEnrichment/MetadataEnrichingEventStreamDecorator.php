<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing\MetadataEnrichment;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\EnrichableDomainMessageInterface;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;

/**
 * Event stream decorator that adds extra metadata.
 */
class MetadataEnrichingEventStreamDecorator implements EventStreamDecoratorInterface
{
    private $metadataEnrichers;

    /**
     * @param array $metadataEnrichers
     */
    public function __construct(array $metadataEnrichers = array())
    {
        $this->metadataEnrichers = $metadataEnrichers;
    }

    public function registerEnricher(MetadataEnricherInterface $enricher)
    {
        $this->metadataEnrichers[] = $enricher;
    }

    /**
     * {@inheritDoc}
     */
    public function decorateForWrite($aggregateType, $aggregateIdentifier, DomainEventStreamInterface $eventStream)
    {
        if (empty($this->metadataEnrichers)) {
            return $eventStream;
        }

        return new DomainEventStream($this->processEventStream($eventStream));
    }

    /**
     * @param DomainEventStreamInterface $eventStream
     *
     * @return array
     */
    protected function processEventStream(DomainEventStreamInterface $eventStream)
    {
        $messages = array();

        foreach ($eventStream as $message) {
            if (!$message instanceof EnrichableDomainMessageInterface) {
                continue;
            }

            $messages[] = $message->andMetadata($this->enrichMetadata());
        }

        return $messages;
    }

    /**
     * @return Metadata
     */
    protected function enrichMetadata()
    {
        $metadata = new Metadata();

        foreach ($this->metadataEnrichers as $metadataEnricher) {
            $metadata = $metadataEnricher->enrich($metadata);
        }

        return $metadata;
    }
}
