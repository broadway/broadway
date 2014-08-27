<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Serializer\Testing;

use Broadway\Serializer\SimpleInterfaceSerializer;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Helper to test if events implement the SerializableInterface contract.
 */
abstract class SerializableEventTestCase extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_serializable()
    {
        $this->assertInstanceOf('Broadway\Serializer\SerializableInterface', $this->createEvent());
    }

    /**
     * @test
     */
    public function serializing_and_deserializing_yields_the_same_object()
    {
        $serializer = new SimpleInterfaceSerializer();
        $event      = $this->createEvent();

        $serialized   = $serializer->serialize($event);
        $deserialized = $serializer->deserialize($serialized);

        $this->assertEquals($event, $deserialized);
    }

    /**
     * @return mixed
     */
    abstract protected function createEvent();
}
