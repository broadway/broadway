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
     * @var string
     */
    private $streamType;

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
     * @param string   $streamType
     * @param string   $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     * @param DateTime $recordedOn
     */
    public function __construct($streamType, $id, $playhead, Metadata $metadata, $payload, DateTime $recordedOn)
    {
        $this->streamType = $streamType;
        $this->id         = $id;
        $this->playhead   = $playhead;
        $this->metadata   = $metadata;
        $this->payload    = $payload;
        $this->recordedOn = $recordedOn;
    }

    /**
     * @return string
     */
    public function getStreamType()
    {
        return $this->streamType;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPlayhead()
    {
        return $this->playhead;
    }

    /**
     * @return Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return DateTime
     */
    public function getRecordedOn()
    {
        return $this->recordedOn;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return strtr(get_class($this->payload), '\\', '.');
    }

    /**
     * @param string   $streamType
     * @param string   $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     *
     * @return DomainMessage
     */
    public static function recordNow($streamType, $id, $playhead, Metadata $metadata, $payload)
    {
        return new DomainMessage($streamType, $id, $playhead, $metadata, $payload, DateTime::now());
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

        return new DomainMessage($this->streamType, $this->id, $this->playhead, $newMetadata, $this->payload, $this->recordedOn);
    }
}
