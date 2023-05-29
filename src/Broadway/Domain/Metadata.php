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

use Broadway\Serializer\Serializable;

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
     */
    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }

    public function serialize(): array
    {
        return $this->values;
    }

    public static function deserialize(array $data): self
    {
        return new self($data);
    }
}
