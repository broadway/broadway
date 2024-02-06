<?php

declare(strict_types=1);

class UserCreated implements Broadway\Serializer\Serializable
{
    public $userId;
    public $name;

    public function __construct(string $userId, string $name)
    {
        $this->userId = $userId;
        $this->name = $name;
    }

    public static function deserialize(array $data)
    {
        return new self(
            $data['userId'],
            $data['name']
        );
    }

    public function serialize(): array
    {
        return [
            'userId' => $this->userId,
            'name' => $this->name,
        ];
    }
}
