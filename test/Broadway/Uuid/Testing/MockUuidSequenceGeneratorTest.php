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

class MockUuidSequenceGeneratorTest extends TestCase
{
    private $uuids = array(
        'e2d0c739-0001-434c-8d7a-03e29b400566',
        'e2d0c739-0002-434c-8d7a-03e29b400566',
        'e2d0c739-0003-434c-8d7a-03e29b400566',
        'e2d0c739-0004-434c-8d7a-03e29b400566',
    );

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

        foreach ($this->uuids as $uuid) {
            $this->assertEquals($uuid, $generator->generate());
        }
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function it_throws_an_exception_when_pool_is_empty()
    {
        $generator = $this->createMockUuidGenerator();

        for ($i = 0; $i < 5; $i++) {
            $generator->generate();
        }
    }

    private function createMockUuidGenerator()
    {
        return new MockUuidSequenceGenerator($this->uuids);
    }
}
