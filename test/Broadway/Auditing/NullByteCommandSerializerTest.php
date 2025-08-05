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

namespace Broadway\Auditing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NullByteCommandSerializerTest extends TestCase
{
    private NullByteCommandSerializer $commandSerializer;

    private MyCommand $command;

    protected function setUp(): void
    {
        $this->commandSerializer = new NullByteCommandSerializer();
        $this->command = new MyCommand();
    }

    #[Test]
    public function it_returns_a_json_string(): void
    {
        $serializedCommand = $this->commandSerializer->serialize($this->command);

        $this->assertTrue(is_array($serializedCommand));

        $expected = [
            'public' => 'public',
            '-*-protected' => 'protected',
            '-Broadway\\Auditing\\MyCommand-private' => 'private',
        ];

        $this->assertEquals($expected, $serializedCommand);
    }
}

final readonly class MyCommand
{
    public function __construct(
        public string $public = 'public',
        protected string $protected = 'protected',
        private string $private = 'private',
    ) {
    }
}
