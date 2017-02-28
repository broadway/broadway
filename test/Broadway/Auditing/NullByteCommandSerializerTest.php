<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Auditing;

use Broadway\TestCase;

class NullByteCommandSerializerTest extends TestCase
{
    private $commandSerializer;
    private $command;

    public function setUp()
    {
        $this->commandSerializer = new NullByteCommandSerializer();
        $this->command           = new MyCommand();
    }

    /**
     * @test
     */
    public function it_returns_a_json_string()
    {
        $serializedCommand = $this->commandSerializer->serialize($this->command);

        $this->assertTrue(is_array($serializedCommand));

        $expected = [
            'public'                                 => 'public',
            '-*-protected'                           => 'protected',
            '-Broadway\\Auditing\\MyCommand-private' => 'private'
        ];

        $this->assertEquals($expected, $serializedCommand);
    }
}

class MyCommand
{
    public $public       = 'public';
    protected $protected = 'protected';
    private $private     = 'private';
}
