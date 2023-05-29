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

namespace Broadway\ReadModel;

/**
 * Abstraction for the storage of read models.
 */
interface Repository
{
    public function save(Identifiable $data): void;

    public function find($id): ?Identifiable;

    /**
     * @return Identifiable[]
     */
    public function findBy(array $fields): array;

    /**
     * @return Identifiable[]
     */
    public function findAll(): array;

    public function remove($id): void;
}
