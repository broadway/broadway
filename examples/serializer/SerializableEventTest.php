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

use Broadway\Serializer\Serializable;
use Broadway\Serializer\Testing\SerializableEventTestCase;

class SerializableEventTest extends SerializableEventTestCase
{
    protected function createEvent(): Serializable
    {
        return new SerializableInviteEvent('c92cba66-7ab9-4e42-9cf2-516813c4537a', 'John');
    }

    #[PHPUnit\Framework\Attributes\Test]
    public function it_should_return_the_correct_object(): void
    {
        $serializedData = [
            'invitationId' => 'c92cba66-7ab9-4e42-9cf2-516813c4537a',
            'name' => 'John',
        ];

        // Create an instance of SerializableInviteEvent
        $serializer = $this->createEvent();
        // Use the `deserialize` method, to convert the data back to the actual object
        $deserialized = $serializer->deserialize($serializedData);

        // Assert that the serializer we instantiated, is equal to the event we have deserialized
        self::assertEquals($serializer, $deserialized);
        // Assert that the data we started out with, is the same data that will be outputted again via the `serialize` method
        self::assertEquals($serializedData, $deserialized->serialize());
    }
}

final readonly class SerializableInviteEvent implements Serializable
{
    public function __construct(
        private string $invitationId,
        private string $name,
    ) {
    }

    public static function deserialize(array $data): self
    {
        return new self($data['invitationId'], $data['name']);
    }

    public function serialize(): array
    {
        return [
            'invitationId' => $this->invitationId,
            'name' => $this->name,
        ];
    }
}
