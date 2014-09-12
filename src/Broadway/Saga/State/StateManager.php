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
use Broadway\UuidGenerator\UuidGeneratorInterface;

class StateManager implements StateManagerInterface
{
    private $repository;
    private $generator;

    public function __construct(RepositoryInterface $repository, UuidGeneratorInterface $generator)
    {
        $this->repository = $repository;
        $this->generator  = $generator;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy($criteria, $sagaId)
    {
        // TODO: Use CreationPolicy to determine whether and how a new state should be created
        if ($criteria instanceof Criteria) {
            return $this->repository->findOneBy($criteria, $sagaId);
        }

        return new State($this->generator->generate());
    }
}
