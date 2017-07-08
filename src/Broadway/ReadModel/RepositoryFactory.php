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

namespace Broadway\ReadModel;

/**
 * Creates repositories.
 */
interface RepositoryFactory
{
    /**
     * @param string $name
     * @param string $class
     *
     * @return Repository
     */
    public function create(string $name, string $class): Repository;
}
