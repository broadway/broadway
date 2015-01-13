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
 * Represents an important change in the domain.
 */
class DomainMessage extends AbstractMessage implements EnrichableDomainMessageInterface
{
    /**
     * Creates a new DomainMessage with all things equal, except metadata.
     *
     * @param Metadata $metadata Metadata to add
     *
     * @return DomainMessage
     */
    public function andMetadata(Metadata $metadata)
    {
        $newMetadata = $this->getMetadata()->merge($metadata);
        $class = static::getMessageClass();

        return new $class($this->getId(), $this->getPlayhead(), $newMetadata, $this->getPayload(), $this->getRecordedOn());
    }
}
