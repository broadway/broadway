<?php

namespace Broadway\Domain;

use Broadway\Serializer\Serializable;

/**
 * @phpstan-type SerializableMetaDataData array<string, mixed>
 *
 * @template T of object
 *
 * @template-extends Serializable<T>
 */
interface SerializableMetaDataInterface extends Serializable
{
    /**
     * @var SerializableMetaDataData $values
     */
    public array $values { get; }
}
