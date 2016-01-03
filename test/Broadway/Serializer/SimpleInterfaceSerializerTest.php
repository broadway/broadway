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

use Broadway\TestCase;

class SimpleInterfaceSerializerTest extends TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new SimpleInterfaceSerializer();
    }

    /**
     * @test
     * @expectedException Broadway\Serializer\SerializationException
     * @expectedExceptionMessage Object 'stdClass' does not implement Broadway\Serializer\SerializableInterface
     */
    public function it_throws_an_exception_if_an_object_does_not_implement_SerializableInterface()
    {
        $this->serializer->serialize(new \stdClass());
    }

    /**
     * @test
     * @expectedException Assert\InvalidArgumentException
     * @expectedExceptionMessage Key 'class' should be set
     * @todo custom exception
     */
    public function it_throws_an_exception_if_class_not_set_in_data()
    {
        $this->serializer->deserialize(array());
    }

    /**
     * @test
     * @expectedException Assert\InvalidArgumentException
     * @expectedExceptionMessage Key 'payload' should be set
     * @todo custom exception
     */
    public function it_throws_an_exception_if_payload_not_set_in_data()
    {
        $this->serializer->deserialize(array('class' => 'SomeClass'));
    }

    /**
     * @test
     */
    public function it_serializes_objects_implementing_SerializableInterface()
    {
        $object = new TestSerializable('bar');

        $this->assertEquals(array(
            'class'   => 'Broadway\Serializer\TestSerializable',
            'payload' => array('foo' => 'bar')
        ), $this->serializer->serialize($object));
    }

    /**
     * @test
     */
    public function it_serializes_objects_implementing_SerializableInterface_on_two_levels()
    {
        $object = new TestSerializable(new TestSerializable('bar'));

        $this->assertEquals(array(
            'class'   => 'Broadway\Serializer\TestSerializable',
            'payload' => array(
                'foo' => array(
                    'class'   => 'Broadway\Serializer\TestSerializable',
                    'payload' => array(
                        'foo' => 'bar'
                    )
                )
            )
        ), $this->serializer->serialize($object));
    }

    /**
     * @test
     */
    public function it_deserializes_classes_implementing_SerializableInterface()
    {
        $data = array('class' => 'Broadway\Serializer\TestSerializable', 'payload' => array('foo' => 'bar'));

        $this->assertEquals(new TestSerializable('bar'), $this->serializer->deserialize($data));
    }

    /**
     * @test
     */
    public function it_deserializes_classes_implementing_SerializableInterface_on_two_levels()
    {
        $data = array(
            'class'   => 'Broadway\Serializer\TestSerializable',
            'payload' => array(
                'foo' => array(
                    'class'   => 'Broadway\Serializer\TestSerializable',
                    'payload' => array(
                        'foo' => 'bar'
                    )
                )
            )
        );

        $this->assertEquals(new TestSerializable(new TestSerializable('bar')), $this->serializer->deserialize($data));
    }

    /**
     * @test
     */
    public function it_can_deserialize_classes_it_has_serialized()
    {
        $object = new TestSerializable('bar');

        $serialized   = $this->serializer->serialize($object);
        $deserialized = $this->serializer->deserialize($serialized);

        $this->assertEquals($object, $deserialized);
    }

    /**
     * @test
     */
    public function it_can_deserialize_classes_it_has_serialized_on_two_levels()
    {
        $object = new TestSerializable(new TestSerializable('bar'));

        $serialized   = $this->serializer->serialize($object);
        $deserialized = $this->serializer->deserialize($serialized);

        $this->assertEquals($object, $deserialized);
    }
}

class TestSerializable implements SerializableInterface
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return this
     */
    public static function deserialize(array $data)
    {
        return new self($data['foo']);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array('foo' => $this->foo);
    }
}
