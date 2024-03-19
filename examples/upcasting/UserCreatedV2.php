<?php

declare(strict_types=1);

class UserCreatedV2 implements Broadway\Serializer\Serializable
{
    public $userId;
    public $name;
    public $surname;
    public $country;

    public function __construct(string $userId, string $name, string $surname, string $country)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->surname = $surname;
        $this->country = $country;
    }

    public static function deserialize(array $data)
    {
        return new self(
            $data['userId'],
            $data['name'],
            $data['surname'],
            $data['country']
        );
    }

    public function serialize(): array
    {
        return [
            'userId' => $this->userId,
            'name' => $this->name,
            'surname' => $this->surname,
            'country' => $this->country,
        ];
    }
}
