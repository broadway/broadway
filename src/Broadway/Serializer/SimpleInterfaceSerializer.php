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

use Assert\Assertion as Assert;

/**
 * Serializer that serializes objects that implement a specific interface.
 */
class SimpleInterfaceSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function serialize($object)
    {
        if (! $object instanceof SerializableInterface) {
            throw new SerializationException(sprintf(
                'Object \'%s\' does not implement Broadway\Serializer\SerializableInterface',
                get_class($object)
            ));
        }

        return [
            'class'   => get_class($object),
            'payload' => $object->serialize()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(array $serializedObject)
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        if (! in_array(SerializableInterface::class, class_implements($serializedObject['class']))) {
            throw new SerializationException(
                sprintf(
                    'Class \'%s\' does not implement Broadway\Serializer\SerializableInterface',
                    $serializedObject['class']
                )
            );
        }

        return $serializedObject['class']::deserialize($serializedObject['payload']);
    }
}
