<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\InMemory;

use Broadway\ReadModel\RepositoryFactory;

/**
 * Creates in-memory repositories.
 */
class InMemoryRepositoryFactory implements RepositoryFactory
{
    /**
     * {@inheritDoc}
     */
    public function create($name, $class)
    {
        return new InMemoryRepository();
    }
}
