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
 * Creates repositories.
 */
interface RepositoryFactoryInterface
{
    /**
     * @param string $name
     * @param string $class
     *
     * @return RepositoryInterface
     */
    public function create($name, $class);
}
