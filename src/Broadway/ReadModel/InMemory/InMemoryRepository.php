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

namespace Broadway\ReadModel\InMemory;

use Broadway\ReadModel\Identifiable;
use Broadway\ReadModel\Repository;
use Broadway\ReadModel\Transferable;

/**
 * In-memory implementation of a read model repository.
 *
 * The in-memory repository is useful for testing code.
 */
final class InMemoryRepository implements Repository, Transferable
{
    private $data = [];

    public function save(Identifiable $model): void
    {
        $this->data[$model->getId()] = $model;
    }

    public function find($id): ?Identifiable
    {
        $id = (string) $id;
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }

        return null;
    }

    public function findBy(array $fields): array
    {
        if (!$fields) {
            return [];
        }

        return array_values(array_filter($this->data, function ($model) use ($fields) {
            foreach ($fields as $field => $value) {
                $getter = 'get'.ucfirst($field);

                $modelValue = $model->$getter();

                if (is_array($modelValue) && !in_array($value, $modelValue)) {
                    return false;
                } elseif (!is_array($modelValue) && $modelValue !== $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function findAll(): array
    {
        return array_values($this->data);
    }

    public function transferTo(Repository $otherRepository): void
    {
        foreach ($this->data as $model) {
            $otherRepository->save($model);
        }
    }

    public function remove($id): void
    {
        unset($this->data[(string) $id]);
    }
}
