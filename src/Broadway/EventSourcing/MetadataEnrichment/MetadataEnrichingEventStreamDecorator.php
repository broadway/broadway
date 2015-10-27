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
    public function __construct(array $metadataEnrichers = [])
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

        $messages = [];

        foreach ($eventStream as $message) {
            $metadata = new Metadata();

            foreach ($this->metadataEnrichers as $metadataEnricher) {
                $metadata = $metadataEnricher->enrich($metadata);
            }

            $messages[] = $message->andMetadata($metadata);
        }

        return new DomainEventStream($messages);
    }
}
