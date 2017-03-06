<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel;

class RepositoryTestReadModel implements SerializableReadModel
{
    private $id;
    private $name;
    private $foo;
    private $array;

    public function __construct($id, $name, $foo, array $array)
    {
        $this->id    = (string) $id;
        $this->name  = $name;
        $this->foo   = $foo;
        $this->array = $array;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getArray()
    {
        return $this->array;
    }

    public function serialize()
    {
        return get_object_vars($this);
    }

    public static function deserialize(array $data)
    {
        return new self($data['id'], $data['name'], $data['foo'], $data['array']);
    }
}
