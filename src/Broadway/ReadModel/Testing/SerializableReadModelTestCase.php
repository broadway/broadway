<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\Testing;

use Broadway\ReadModel\SerializableReadModelInterface;
use Broadway\Serializer\SerializableInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Base test case that can be used to test a serializable read model.
 */
abstract class SerializableReadModelTestCase extends TestCase
{
    /**
     * @test
     */
    public function its_serializable()
    {
        $this->assertInstanceOf(SerializableInterface::class, $this->createSerializableReadModel());
    }

    /**
     * @test
     */
    public function serializing_and_deserializing_yields_the_same_object()
    {
        $serializer = new SimpleInterfaceSerializer();
        $readModel  = $this->createSerializableReadModel();

        $serialized   = $serializer->serialize($readModel);
        $deserialized = $serializer->deserialize($serialized);

        $this->assertEquals($readModel, $deserialized);
    }

    /**
     * @return SerializableReadModelInterface
     */
    abstract protected function createSerializableReadModel();
}
