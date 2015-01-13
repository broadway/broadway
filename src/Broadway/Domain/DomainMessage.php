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
final class DomainMessage
{
    /**
     * @var int
     */
    private $playhead;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $recordedOn;

    /**
     * @param string   $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     * @param DateTime $recordedOn
     */
    public function __construct($id, $playhead, Metadata $metadata, $payload, DateTime $recordedOn)
    {
        $this->id         = $id;
        $this->playhead   = $playhead;
        $this->metadata   = $metadata;
        $this->payload    = $payload;
        $this->recordedOn = $recordedOn;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getPlayhead()
    {
        return $this->playhead;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordedOn()
    {
        return $this->recordedOn;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return strtr(get_class($this->payload), '\\', '.');
    }

    /**
     * @param string   $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     *
     * @return DomainMessage
     */
    public static function recordNow($id, $playhead, Metadata $metadata, $payload)
    {
        return new DomainMessage($id, $playhead, $metadata, $payload, DateTime::now());
    }

    /**
     * Creates a new DomainMessage with all things equal, except metadata.
     *
     * @param Metadata $metadata Metadata to add
     *
     * @return DomainMessage
     */
    public function andMetadata(Metadata $metadata)
    {
        $newMetadata = $this->metadata->merge($metadata);

        return new DomainMessage($this->id, $this->playhead, $newMetadata, $this->payload, $this->recordedOn);
    }
}
