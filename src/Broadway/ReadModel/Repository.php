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
interface Repository
{
    public function save(Identifiable $data);

    /**
     * @param string $id
     *
     * @return Identifiable|null
     */
    public function find($id);

    /**
     * @param array $fields
     *
     * @return Identifiable[]
     */
    public function findBy(array $fields);

    /**
     * @return Identifiable[]
     */
    public function findAll();

    /**
     * @param string $id
     */
    public function remove($id);
}
