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

namespace Broadway\Serializer\Testing;

use Broadway\Serializer\Serializable;
use Broadway\Serializer\SimpleInterfaceSerializer;
use PHPUnit\Framework\TestCase;

/**
 * Helper to test if events implement the Serializable contract.
 */
abstract class SerializableEventTestCase extends TestCase
{
    /**
     * @test
     */
    public function its_serializable()
    {
        $this->assertInstanceOf(Serializable::class, $this->createEvent());
    }

    /**
     * @test
     */
    public function serializing_and_deserializing_yields_the_same_object()
    {
        $serializer = new SimpleInterfaceSerializer();
        $event = $this->createEvent();

        $serialized = $serializer->serialize($event);
        $deserialized = $serializer->deserialize($serialized);

        $this->assertEquals($event, $deserialized);
    }

    abstract protected function createEvent();
}
