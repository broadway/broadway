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

namespace Broadway\Repository;

use Broadway\Domain\AggregateRoot;

/**
 * Repository for aggregate roots.
 */
interface Repository
{
    /**
     * Adds the aggregate to the repository.
     */
    public function save(AggregateRoot $aggregate): void;

    /**
     * Loads an aggregate from the given id.
     *
     * @throws AggregateNotFoundException
     */
    public function load($id): AggregateRoot;
}
