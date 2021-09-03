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

use MicroModule\Broadway\Serializer\Serializable;
use MicroModule\Broadway\Serializer\Testing\SerializableEventTestCase;

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

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self($data['invitationId'], $data['name']);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'invitationId' => $this->invitationId,
            'name' => $this->name,
        ];
    }
}
