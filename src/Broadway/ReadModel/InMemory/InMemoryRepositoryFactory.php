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

namespace MicroModule\Broadway\ReadModel\InMemory;

use MicroModule\Broadway\ReadModel\Repository;
use MicroModule\Broadway\ReadModel\RepositoryFactory;

/**
 * Creates in-memory repositories.
 */
final class InMemoryRepositoryFactory implements RepositoryFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(string $name, string $class): Repository
    {
        return new InMemoryRepository();
    }
}
