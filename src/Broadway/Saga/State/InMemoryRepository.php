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

class InMemoryRepository implements RepositoryInterface
{
    private $states = [];

    /**
     * {@inheritDoc}
     */
    public function findOneBy(Criteria $criteria, $sagaId)
    {
        if (! isset($this->states[$sagaId])) {
            return null;
        }

        $states = $this->states[$sagaId];

        foreach ($criteria->getComparisons() as $key => $value) {
            $states = array_filter($states, function ($elem) use ($key, $value) {
                $stateValue = $elem->get($key);

                return is_array($stateValue) ? in_array($value, $stateValue) : $value === $stateValue;
            });
        }

        $amount = count($states);

        if (1 === $amount) {
            return current($states);
        }

        if ($amount > 1) {
            throw new RepositoryException('Multiple saga state instances found.');
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(State $state, $sagaId)
    {
        if ($state->isDone()) {
            unset($this->states[$sagaId][$state->getId()]);
        } else {
            $this->states[$sagaId][$state->getId()] = $state;
        }
    }
}
