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

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReflectionSerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = new ReflectionSerializer();
    }

    /**
     * @test
     *
     * @todo custom exception
     */
    public function it_throws_an_exception_if_class_not_set_in_data()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'class\' should be set');

        $this->serializer->deserialize([]);
    }

    /**
     * @test
     *
     * @todo custom exception
     */
    public function it_throws_an_exception_if_payload_not_set_in_data()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key \'payload\' should be set');

        $this->serializer->deserialize(['class' => 'SomeClass']);
    }

    /**
     * @test
     */
    public function it_serializes_objects()
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

    /**
     * @test
     */
    public function it_deserializes_array()
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

    /**
     * @test
     * @dataProvider serializable_test_data
     */
    public function it_serializes($data)
    {
        $object = new TestReflectableObject([], $data);

        $serialized = $this->serializer->serialize($object);

        $deserialized = $this->serializer->deserialize($serialized);

        $this->assertInstanceOf(TestReflectableObject::class, $deserialized);
        $this->assertEquals($object, $deserialized);
    }

    /**
     * @test
     * @dataProvider serializable_test_data
     */
    public function it_fails_to_serialize($data)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->serializer->serialize($data);
    }

    public function serializable_test_data()
    {
        return [
            'null' => [null],
            'integer' => [0],
            'float' => [3.14],
            'string' => ['impossible'],
            'array' => [[]],
            'object' => [(object)[]],
        ];
    }
}

class TestReflectableObject
{
    private $simpleArray;
    private $value;

    public function __construct(array $simpleArray, $value)
    {
        $this->simpleArray = $simpleArray;
        $this->value = $value;
    }
}

class TestReflectable
{
    private $arrayOfObjects;
    private $object;
    private $simpleValue;

    public function __construct(array $arrayOfObjects, $object, $simpleValue)
    {
        $this->arrayOfObjects = $arrayOfObjects;
        $this->object = $object;
        $this->simpleValue = $simpleValue;
    }
}
