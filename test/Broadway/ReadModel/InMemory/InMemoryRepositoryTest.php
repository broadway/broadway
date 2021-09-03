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

namespace MicroModule\Broadway\ReadModel\InMemory;

use MicroModule\Broadway\ReadModel\Repository;
use MicroModule\Broadway\ReadModel\Testing\RepositoryTestCase;
use MicroModule\Broadway\ReadModel\Testing\RepositoryTestReadModel;

class InMemoryRepositoryTest extends RepositoryTestCase
{
    protected function createRepository(): Repository
    {
        return new InMemoryRepository();
    }

    /**
     * @test
     */
    public function it_can_be_transferred_to_another_repository()
    {
        $repository = $this->createRepository();

        $model1 = $this->createReadModel('1', 'othillo', 'bar');
        $model2 = $this->createReadModel('2', 'asm89', 'baz');

        $this->repository->save($model1);
        $this->repository->save($model2);

        $targetRepository = new InMemoryRepository();

        $repository->transferTo($targetRepository);

        $this->assertEquals($targetRepository->findAll(), $repository->findAll());
    }

    protected function createReadModel($id, $name, $foo, array $array = []): RepositoryTestReadModel
    {
        return new RepositoryTestReadModel($id, $name, $foo, $array);
    }
}
