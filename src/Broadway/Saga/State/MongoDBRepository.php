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
use Doctrine\MongoDB\Collection;

class MongoDBRepository implements RepositoryInterface
{
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(Criteria $criteria, $sagaId)
    {
        $query   = $this->createQuery($criteria, $sagaId);
        $results = $query->execute();
        $count   = count($results);

        if ($count === 1) {
            return State::deserialize(current($results->toArray()));
        }

        if ($count > 1) {
            throw new RepositoryException('Multiple saga state instances found.');
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(State $state, $sagaId)
    {
        $serializedState            = $state->serialize();
        $serializedState['_id']     = $serializedState['id'];
        $serializedState['sagaId']  = $sagaId;
        $serializedState['removed'] = $state->isDone();

        $this->collection->save($serializedState);
    }

    private function createQuery(Criteria $criteria, $sagaId)
    {
        $comparisons = $criteria->getComparisons();
        $wheres      = [];

        foreach ($comparisons as $key => $value) {
            $wheres['values.' . $key] = $value;
        }

        $queryBuilder = $this->collection->createQueryBuilder()
            ->addAnd($wheres)
            ->addAnd(['removed' => false, 'sagaId' => $sagaId]);

        return $queryBuilder->getQuery();
    }
}
