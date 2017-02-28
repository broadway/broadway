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

use Broadway\Serializer\Serializable;

/**
 * Metadata adding extra information to the DomainMessage.
 */
class Metadata implements Serializable
{
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Merges the values of this and the other instance.
     *
     * @param Metadata $otherMetadata
     *
     * @return Metadata a new instance
     */
    public function merge(Metadata $otherMetadata)
    {
        $mergedValues = array_merge($this->values, $otherMetadata->values);

        return new Metadata($mergedValues);
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return $this->values;
    }

    /**
     * Helper method to construct an instance containing the key and value.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return Metadata
     */
    public static function kv($key, $value)
    {
        return new Metadata([$key => $value]);
    }

    /**
     * @param array $data
     *
     * @return Metadata
     */
    public static function deserialize(array $data)
    {
        return new Metadata($data);
    }
}
