<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Uuid\Testing;

use Broadway\TestCase;

class MockUuidGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_a_string()
    {
        $generator = $this->createMockUuidGenerator();
        $uuid      = $generator->generate();

        $this->assertInternalType('string', $uuid);
    }

    /**
     * @test
     */
    public function it_generates_the_same_string()
    {
        $generator = $this->createMockUuidGenerator();

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals('e2d0c739-53ac-434c-8d7a-03e29b400566', $generator->generate());
        }
    }

    private function createMockUuidGenerator()
    {
        return new MockUuidGenerator('e2d0c739-53ac-434c-8d7a-03e29b400566');
    }
}
