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

namespace MicroModule\Broadway\Domain;

use MicroModule\Broadway\Serializer\Serializable;

/**
 * Metadata adding extra information to the DomainMessage.
 */
final class Metadata implements Serializable
{
    private $values = [];

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Helper method to construct an instance containing the key and value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public static function kv($key, $value): self
    {
        return new self([$key => $value]);
    }

    /**
     * Merges the values of this and the other instance.
     */
    public function merge(self $otherMetadata): self
    {
        return new self(array_merge($this->values, $otherMetadata->values));
    }

    /**
     * Returns an array with all metadata.
     *
     * @return mixed[]
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Get a specific metadata value based on key.
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return $this->values;
    }

    /**
     * @return Metadata
     */
    public static function deserialize(array $data): self
    {
        return new self($data);
    }
}
