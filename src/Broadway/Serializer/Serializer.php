<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Serializer;

/**
 * Interface for classes that can serialize arbitrary objects into arrays with
 * scalars (for now).
 */
interface Serializer
{
    /**
     * @return array
     *
     * @throws SerializationException
     */
    public function serialize($object);

    /**
     * @param array $serializedObject
     *
     * @return mixed
     *
     * @throws SerializationException
     */
    public function deserialize(array $serializedObject);
}
