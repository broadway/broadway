<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Repository;

use Broadway\Domain\AggregateRoot;

/**
 * Repository for aggregate roots.
 */
interface Repository
{
    /**
     * Adds the aggregate to the repository.
     *
     * @param AggregateRoot $aggregate
     */
    public function save(AggregateRoot $aggregate);

    /**
     * Loads an aggregate from the given id.
     *
     * @param mixed $id
     *
     * @throws AggregateNotFoundException
     *
     * @return AggregateRoot
     */
    public function load($id): AggregateRoot;
}
