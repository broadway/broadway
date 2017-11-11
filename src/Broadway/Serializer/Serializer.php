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

namespace Broadway\Serializer;

/**
 * Interface for classes that can serialize arbitrary objects into arrays with
 * scalars (for now).
 */
interface Serializer
{
    /**
     * @throws SerializationException
     *
     * @return array
     */
    public function serialize($object): array;

    /**
     * @param array $serializedObject
     *
     * @throws SerializationException
     *
     * @return mixed
     */
    public function deserialize(array $serializedObject);
}
