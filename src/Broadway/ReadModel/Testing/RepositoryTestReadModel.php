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

namespace Broadway\ReadModel\Testing;

use Broadway\ReadModel\SerializableReadModel;

class RepositoryTestReadModel implements SerializableReadModel
{
    private $id;
    private $name;
    private $foo;
    private $array;

    public function __construct($id, string $name, $foo, array $array)
    {
        $this->id = (string) $id;
        $this->name = $name;
        $this->foo = $foo;
        $this->array = $array;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function serialize(): array
    {
        return get_object_vars($this);
    }

    public static function deserialize(array $data)
    {
        return new self($data['id'], $data['name'], $data['foo'], $data['array']);
    }
}
