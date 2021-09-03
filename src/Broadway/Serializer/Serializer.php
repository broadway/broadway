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

namespace MicroModule\Broadway\Serializer;

/**
 * Interface for classes that can serialize arbitrary objects into arrays with
 * scalars (for now).
 */
interface Serializer
{
    /**
     * @throws SerializationException
     *
     * @param mixed $object
     */
    public function serialize($object): array;

    /**
     * @throws SerializationException
     *
     * @return mixed
     */
    public function deserialize(array $serializedObject);
}
