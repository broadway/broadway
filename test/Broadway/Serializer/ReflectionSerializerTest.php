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

class ReflectionSerializerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ReflectionSerializer();
    }

    /**
     * @todo custom exception
     */
    #[Test]
    public function it_throws_an_exception_if_class_not_set_in_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'class\' should be set');

        $this->serializer->deserialize([]);
    }

    /**
     * @todo custom exception
     */
    #[Test]
    public function it_throws_an_exception_if_payload_not_set_in_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'payload\' should be set');

        $this->serializer->deserialize(['class' => 'SomeClass']);
    }

    #[Test]
    public function it_serializes_objects(): void
    {
        $object = new TestReflectable(
            [new TestReflectableObject(['A', 1, 1.0], 11)],
            new TestReflectableObject(['B', 2, 2.0], 22),
            33
        );

        $this->assertEquals([
            'class' => 'Broadway\Serializer\TestReflectable',
            'payload' => [
                'simpleValue' => 33,
                'arrayOfObjects' => [
                    [
                        'class' => 'Broadway\Serializer\TestReflectableObject',
                        'payload' => [
                            'simpleArray' => ['A', 1, 1.0],
                            'value' => 11,
                        ],
                    ],
                ],
                'object' => [
                    'class' => 'Broadway\Serializer\TestReflectableObject',
                    'payload' => [
                        'simpleArray' => ['B', 2, 2.0],
                        'value' => 22,
                    ],
                ],
            ],
        ], $this->serializer->serialize($object));
    }

    #[Test]
    public function it_deserializes_array(): void
    {
        $data = [
            'class' => 'Broadway\Serializer\TestReflectable',
            'payload' => [
                'simpleValue' => 33,
                'arrayOfObjects' => [
                    [
                        'class' => 'Broadway\Serializer\TestReflectableObject',
                        'payload' => [
                            'simpleArray' => ['A', 1, 1.0],
                            'value' => 11,
                        ],
                    ],
                ],
                'object' => [
                    'class' => 'Broadway\Serializer\TestReflectableObject',
                    'payload' => [
                        'simpleArray' => ['B', 2, 2.0],
                        'value' => 22,
                    ],
                ],
            ],
        ];

        $object = new TestReflectable(
            [new TestReflectableObject(['A', 1, 1.0], 11)],
            new TestReflectableObject(['B', 2, 2.0], 22),
            33
        );

        $this->assertEquals($object, $this->serializer->deserialize($data));
    }
}

final readonly class TestReflectableObject
{
    public function __construct(
        public array $simpleArray,
        public int $value,
    ) {
    }
}

final readonly class TestReflectable
{
    public function __construct(
        public array $arrayOfObjects,
        public object $object,
        public int $simpleValue,
    ) {
    }
}
