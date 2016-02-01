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
use MongoDB\Collection;

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
        $filter = $this->getCollectionFilter($criteria, $sagaId);
        $cursor = $this->collection->find($filter, ['typeMap' => ['root' => 'array', 'document' => 'array']]);
        $count  = $this->collection->count($filter);

        if ($count === 1) {
            return State::deserialize(current($cursor->toArray()));
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

        $this->collection->replaceOne(['_id' => $serializedState['_id']], $serializedState, ['upsert' => true]);
    }

    private function getCollectionFilter(Criteria $criteria, $sagaId)
    {
        $comparisons = $criteria->getComparisons();
        $filter      = [
          'removed' => false,
          'sagaId'  => $sagaId,
        ];

        foreach ($comparisons as $key => $value) {
            $filter['values.' . $key] = $value;
        }

        return $filter;
    }
}
