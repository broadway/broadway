<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Repository;

use Broadway\Domain\AggregateRoot;

/**
 * Repository for aggregate roots.
 */
interface RepositoryInterface
{
    public function add(AggregateRoot $aggregate);

    /**
     * Loads an aggregate from the given id.
     *
     * @param mixed $id
     *
     * @throws AggregateNotFoundException
     */
    public function load($id);
}
