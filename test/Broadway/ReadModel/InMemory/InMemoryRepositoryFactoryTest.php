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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InMemoryRepositoryFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_an_in_memory_repository(): void
    {
        $repository = new InMemoryRepository();
        $factory = new InMemoryRepositoryFactory();

        $this->assertEquals($repository, $factory->create('test', 'class'));
    }
}
