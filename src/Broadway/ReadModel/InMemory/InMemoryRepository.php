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

use Broadway\ReadModel\ReadModel;
use Broadway\ReadModel\Repository;
use Broadway\ReadModel\TransfersToAnotherRepository;

/**
 * In-memory implementation of a read model repository.
 *
 * The in-memory repository is useful for testing code.
 */
class InMemoryRepository extends Repository implements TransfersToAnotherRepository
{
    private $data = array();

    /**
     * {@inhericDoc}
     */
    public function save(ReadModel $model)
    {
        $this->data[$model->getId()] = $model;
    }

    /**
     * {@inhericDoc}
     */
    public function find($id)
    {
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $fields)
    {
        if (! $fields) {
            return array();
        }

        return array_values(array_filter($this->data, function ($model) use ($fields) {
            foreach ($fields as $field => $value) {
                $getter = 'get' . ucfirst($field);

                $modelValue = $model->$getter();

                if (is_array($modelValue) && ! in_array($value, $modelValue)) {
                    return false;
                } elseif (! is_array($modelValue) && $modelValue !== $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return array_values($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function transferTo(Repository $otherRepository)
    {
        foreach ($this->data as $model) {
            $otherRepository->save($model);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        unset($this->data[$id]);
    }
}
