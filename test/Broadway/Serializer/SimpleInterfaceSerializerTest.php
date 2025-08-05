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

use Assert\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SimpleInterfaceSerializerTest extends TestCase
{
    private SimpleInterfaceSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new SimpleInterfaceSerializer();
    }

    #[Test]
    public function it_throws_an_exception_if_an_object_does_not_implement_serializable(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage(sprintf(
            'Object \'%s\' does not implement %s',
            \stdClass::class,
            Serializable::class
        ));

        $this->serializer->serialize(new \stdClass());
    }

    #[Test]
    public function it_throws_an_exception_if_class_not_set_in_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'class\' should be set');

        $this->serializer->deserialize([]);
    }

    #[Test]
    public function it_throws_an_exception_if_payload_not_set_in_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'payload\' should be set');

        $this->serializer->deserialize(['class' => 'SomeClass']);
    }

    #[Test]
    public function it_serializes_objects_implementing_serializable(): void
    {
        $object = new TestSerializable('bar');

        $this->assertEquals([
            'class' => 'Broadway\Serializer\TestSerializable',
            'payload' => ['foo' => 'bar'],
        ], $this->serializer->serialize($object));
    }

    #[Test]
    public function it_deserializes_classes_implementing_serializable(): void
    {
        $data = ['class' => 'Broadway\Serializer\TestSerializable', 'payload' => ['foo' => 'bar']];

        $this->assertEquals(new TestSerializable('bar'), $this->serializer->deserialize($data));
    }

    #[Test]
    public function it_can_deserialize_classes_it_has_serialized(): void
    {
        $object = new TestSerializable('bar');

        $serialized = $this->serializer->serialize($object);
        $deserialized = $this->serializer->deserialize($serialized);

        $this->assertEquals($object, $deserialized);
    }
}

final readonly class TestSerializable implements Serializable
{
    public function __construct(public string $foo)
    {
    }

    public static function deserialize(array $data): self
    {
        return new self($data['foo']);
    }

    public function serialize(): array
    {
        return ['foo' => $this->foo];
    }
}
