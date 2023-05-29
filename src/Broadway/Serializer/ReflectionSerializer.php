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

namespace Broadway\Serializer;

use Assert\Assertion as Assert;

/**
 * Serializer that deeply serializes objects with the help of reflection.
 */
class ReflectionSerializer implements Serializer
{
    public function serialize($object): array
    {
        return $this->serializeObjectRecursively($object);
    }

    private function serializeValue($value)
    {
        if (is_object($value)) {
            return $this->serializeObjectRecursively($value);
        } elseif (is_array($value)) {
            return $this->serializeArrayRecursively($value);
        }

        return $value;
    }

    private function serializeArrayRecursively(array $array): array
    {
        $data = [];
        foreach ($array as $key => $value) {
            $data[$key] = $this->serializeValue($value);
        }

        return $data;
    }

    /**
     * @param object $object
     */
    private function serializeObjectRecursively($object): array
    {
        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties();

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();

            $property->setAccessible(true);
            $value = $property->getValue($object);
            $property->setAccessible(false);

            $data[$name] = $this->serializeValue($value);
        }

        return [
            'class' => get_class($object),
            'payload' => $data,
        ];
    }

    public function deserialize(array $serializedObject)
    {
        return $this->deserializeObjectRecursively($serializedObject);
    }

    private function deserializeValue($value)
    {
        if (is_array($value) && isset($value['class']) && isset($value['payload'])) {
            return $this->deserializeObjectRecursively($value);
        } elseif (is_array($value)) {
            return $this->deserializeArrayRecursively($value);
        }

        return $value;
    }

    private function deserializeArrayRecursively(array $array): array
    {
        $data = [];
        foreach ($array as $key => $value) {
            $data[$key] = $this->deserializeValue($value);
        }

        return $data;
    }

    /**
     * @param array $serializedObject
     *
     * @return object
     */
    private function deserializeObjectRecursively($serializedObject)
    {
        Assert::keyExists($serializedObject, 'class', "Key 'class' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        $reflection = new \ReflectionClass($serializedObject['class']);
        $properties = $reflection->getProperties();
        $object = $reflection->newInstanceWithoutConstructor();

        foreach ($serializedObject['payload'] as $name => $value) {
            $matchedProperty = $this->findProperty($properties, $name);
            if (null === $matchedProperty) {
                throw new SerializationException(sprintf('Property \'%s\' not found for object \'%s\'', $name, $serializedObject['class']));
            }

            $value = $this->deserializeValue($value);

            $matchedProperty->setAccessible(true);
            $matchedProperty->setValue($object, $value);
            $matchedProperty->setAccessible(false);
        }

        return $object;
    }

    /**
     * @param \ReflectionProperty[] $properties
     */
    private function findProperty(array $properties, string $name): ?\ReflectionProperty
    {
        foreach ($properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }
}
