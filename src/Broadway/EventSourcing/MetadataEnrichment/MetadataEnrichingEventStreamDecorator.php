<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventSourcing\MetadataEnrichment;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecorator;

/**
 * Event stream decorator that adds extra metadata.
 */
final class MetadataEnrichingEventStreamDecorator implements EventStreamDecorator
{
    private $metadataEnrichers;

    /**
     * @param MetadataEnricher[] $metadataEnrichers
     */
    public function __construct(array $metadataEnrichers = [])
    {
        $this->metadataEnrichers = $metadataEnrichers;
    }

    public function registerEnricher(MetadataEnricher $enricher): void
    {
        $this->metadataEnrichers[] = $enricher;
    }

    public function decorateForWrite(string $aggregateType, string $aggregateIdentifier, DomainEventStream $eventStream): DomainEventStream
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
