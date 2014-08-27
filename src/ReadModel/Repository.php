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

/**
 * Abstraction for the storage of read models.
 */
abstract class Repository
{
    abstract public function save(ReadModel $data);

    /**
     * @param string $id
     *
     * @return ReadModel|null
     */
    abstract public function find($id);

    /**
     * @param array $fields
     *
     * @return ReadModel[]
     */
    abstract public function findBy(array $fields);

    /**
     * @return ReadModel[]
     */
    abstract public function findAll();

    /**
     * @param string $id
     */
    abstract public function remove($id);
}
