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
    protected function createEvent()
    {
        return new SerializableInviteEvent('invitationId', 'name');
    }
}

class SerializableInviteEvent implements Serializable
{
    private $invitationId;
    private $name;

    public function __construct(string $invitationId, string $name)
    {
        $this->invitationId = $invitationId;
        $this->name = $name;
    }

    public static function deserialize(array $data)
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
