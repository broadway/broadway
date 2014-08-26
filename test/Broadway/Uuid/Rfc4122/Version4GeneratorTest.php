<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Uuid\Rfc4122;

use Broadway\TestCase;
use Rhumsaa\Uuid\Uuid;

class Version4GeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_generate_a_string()
    {
        $generator = new Version4Generator();
        $uuid = $generator->generate();

        $this->assertInternalType('string', $uuid);
    }

    /**
     * @test
     */
    public function it_should_generate_a_version_4_uuid()
    {
        $generator = new Version4Generator();
        $uuid = $generator->generate();

        $uuidObject = Uuid::fromString($uuid);

        $this->assertEquals(4 , $uuidObject->getVersion());
    }
}

