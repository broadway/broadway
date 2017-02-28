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

use Broadway\Domain\Metadata;

/**
 * Adds extra metadata to already existing metadata.
 */
interface MetadataEnricher
{
    /**
     * @return Metadata
     */
    public function enrich(Metadata $metadata);
}
