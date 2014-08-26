<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\State;

use Broadway\Saga\State;

interface RepositoryInterface
{
    /**
     * @param string $sagaId
     *
     * @return State
     *
     * @throws RepositoryException if 0 or > 1 found
     * @todo specific exception
     */
    public function findOneBy(Criteria $criteria, $sagaId);

    public function save(State $state, $sagaId);
}
