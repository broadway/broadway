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

use Broadway\Uuid\UuidGeneratorInterface;

/**
 * Mock uuid generator that always generates the same id.
 */
class MockUuidGenerator implements UuidGeneratorInterface
{
    private $uuid;

    /**
     * @param string $uuid
     */
    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return $this->uuid;
    }
}
