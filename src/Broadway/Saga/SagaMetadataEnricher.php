<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga;

use Broadway\Domain\Metadata;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnricherInterface;

class SagaMetadataEnricher implements MetadataEnricherInterface
{
    private $sagaData = [];

    public function postHandleSaga($type, $id)
    {
        $this->sagaData = ['type' => $type, 'state_id' => $id];
    }

    public function enrich(Metadata $metadata)
    {
        if (count($this->sagaData) === 0) {
            return $metadata;
        }

        $newMetadata = new Metadata(['saga' => $this->sagaData]);
        $metadata    = $metadata->merge($newMetadata);

        return $metadata;
    }
}
