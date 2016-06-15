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

        return array(
            'class'   => get_class($object),
            'payload' => $this->serializePayload($object->serialize())
        );
    }

    /**
     * Recursively serialize the object
     *
     * @param  array $serialized
     * @return array
     */
    private function serializePayload(array $serialized)
    {
        $payload = array();

        foreach ($serialized as $name => $serializedItem) {
            $payload[$name] = ($serializedItem instanceof SerializableInterface) ?
                $this->serialize($serializedItem) : $serializedItem;
        }

        return $payload;
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize(array $serializedObject)
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        if (! in_array('Broadway\Serializer\SerializableInterface', class_implements($serializedObject['class']))) {
            throw new SerializationException(
                sprintf(
                    'Class \'%s\' does not implement Broadway\Serializer\SerializableInterface',
                    $serializedObject['class']
                )
            );
        }

        return $serializedObject['class']::deserialize($this->deserializePayload($serializedObject['payload']));
    }

    /**
     * Recursively deserialize the payload
     *
     * @param  array $payload
     * @return array
     */
    private function deserializePayload(array $payload)
    {
        $processedPayload = array();

        foreach ($payload as $name => $attribute) {
            $processedPayload[$name] = (self::canDeserialize($attribute)) ?
                $this->deserialize($attribute) : $attribute;
        }

        return $processedPayload;
    }

    /**
     * Checks if the attribute is a deserializable object
     *
     * @param  $attribute
     * @return bool
     */
    private static function canDeserialize($attribute)
    {
        return is_array($attribute) && isset($attribute['class']) && isset($attribute['payload']);
    }
}
