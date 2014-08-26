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

use Broadway\TestCase;

class InMemoryRepositoryFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_an_in_memory_repository()
    {
        $repository = new InMemoryRepository();
        $factory    = new InMemoryRepositoryFactory();

        $this->assertEquals($repository, $factory->create('test', 'class'));
    }
}
