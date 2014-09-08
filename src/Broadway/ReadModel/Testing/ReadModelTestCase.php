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

use Broadway\ReadModel\ReadModelInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Base test case that can be used to test a read model.
 */
abstract class ReadModelTestCase extends TestCase
{
    /**
     * @test
     */
    public function its_serializable()
    {
        $this->assertInstanceOf('Broadway\Serializer\SerializableInterface', $this->createReadModel());
    }

    /**
     * @test
     */
    public function serializing_and_deserializing_yields_the_same_object()
    {
        $serializer = new SimpleInterfaceSerializer();
        $readModel  = $this->createReadModel();

        $serialized   = $serializer->serialize($readModel);
        $deserialized = $serializer->deserialize($serialized);

        $this->assertEquals($readModel, $deserialized);
    }

    /**
     * @return ReadModelInterface
     */
    abstract protected function createReadModel();
}
