<?php

declare(strict_types=1);

use Broadway\EventSourcing\EventSourcedAggregateRoot;

class User extends EventSourcedAggregateRoot
{
    private $userId;
    private $name;
    private $surname;
    private $age;
    private $country;

    public static function create(string $userId, string $name, string $surname, int $age, string $country): self
    {
        $user = new self();
        $user->apply(new UserCreatedV3($userId, $name, $surname, $age, $country));

        return $user;
    }

    protected function applyUserCreatedV3(UserCreatedV3 $event): void
    {
        $this->userId = $event->userId;
        $this->name = $event->name;
        $this->surname = $event->surname;
        $this->age = $event->age;
        $this->country = $event->country;
    }

    public function getAggregateRootId(): string
    {
        return $this->userId;
    }

    public function name(): string
    {
        return sprintf('%s %s of age: %d', $this->name, $this->surname, $this->age);
    }

    public function age(): int
    {
        return $this->age;
    }

    public function country(): string
    {
        return $this->country;
    }
}
