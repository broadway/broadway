<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Domain;

/**
 * Represents an important change in the domain that can later be enriched with more metadata.
 */
interface EnrichableDomainMessageInterface extends DomainMessageInterface
{
    /**
     * Merges given Metadata with the current Metadata set on the Domain Message
     *
     * @param Metadata $metadata
     *
     * @return Metadata
     */
    public function andMetadata(Metadata $metadata);
}
