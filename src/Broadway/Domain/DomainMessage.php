<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
     * @param mixed    $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     * @param DateTime $recordedOn
     */
    public function __construct($id, int $playhead, Metadata $metadata, $payload, DateTime $recordedOn)
    {
        $this->id = (string) $id;
        $this->playhead = $playhead;
        $this->metadata = $metadata;
        $this->payload = $payload;
        $this->recordedOn = $recordedOn;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
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
    public function getRecordedOn(): DateTime
    {
        return $this->recordedOn;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return strtr(get_class($this->payload), '\\', '.');
    }

    /**
     * @param mixed    $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     *
     * @return DomainMessage
     */
    public static function recordNow($id, int $playhead, Metadata $metadata, $payload): self
    {
        return new self($id, $playhead, $metadata, $payload, DateTime::now());
    }

    /**
     * Creates a new DomainMessage with all things equal, except metadata.
     *
     * @param Metadata $metadata Metadata to add
     *
     * @return DomainMessage
     */
    public function andMetadata(Metadata $metadata): self
    {
        $newMetadata = $this->metadata->merge($metadata);

        return new self($this->id, $this->playhead, $newMetadata, $this->payload, $this->recordedOn);
    }
}
