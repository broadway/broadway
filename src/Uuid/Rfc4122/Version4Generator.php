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

use Broadway\Uuid\UuidGenerator;
use Rhumsaa\Uuid\Uuid;

/**
 * Generates a version4 uuid as defined in RFC 4122.
 */
class Version4Generator extends UuidGenerator
{
    /**
     * @return string
     */
    public function generate()
    {
        return Uuid::uuid4()->toString();
    }
}
