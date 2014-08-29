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

use RuntimeException;
use Broadway\Uuid\UuidGeneratorInterface;

/**
 * Mock uuid generator that always generates a given sequence of uuids.
 */
class MockUuidSequenceGenerator implements UuidGeneratorInterface
{
    private $uuids;

    /**
     * @param string[] $uuids
     */
    public function __construct(array $uuids)
    {
        $this->uuids = (array) $uuids;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if (count($this->uuids) === 0) {
            throw new RuntimeException('No more uuids in sequence');
        }

        return array_shift($this->uuids);
    }
}
