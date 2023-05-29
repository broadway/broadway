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

    private $payload;

    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $recordedOn;

    public function __construct($id, int $playhead, Metadata $metadata, $payload, DateTime $recordedOn)
    {
        $this->id = (string) $id;
        $this->playhead = $playhead;
        $this->metadata = $metadata;
        $this->payload = $payload;
        $this->recordedOn = $recordedOn;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getRecordedOn(): DateTime
    {
        return $this->recordedOn;
    }

    public function getType(): string
    {
        return strtr(get_class($this->payload), '\\', '.');
    }

    public static function recordNow($id, int $playhead, Metadata $metadata, $payload): self
    {
        return new self($id, $playhead, $metadata, $payload, DateTime::now());
    }

    /**
     * Creates a new DomainMessage with all things equal, except metadata.
     *
     * @param Metadata $metadata Metadata to add
     */
    public function andMetadata(Metadata $metadata): self
    {
        $newMetadata = $this->metadata->merge($metadata);

        return new self($this->id, $this->playhead, $newMetadata, $this->payload, $this->recordedOn);
    }
}
