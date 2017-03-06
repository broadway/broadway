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

/**
 * Command serializer that uses php hacks to get the data from a command.
 *
 * There are many other ways to implement serialization on commands, but since
 * this is only for logging purposes we get away with this solution for now.
 */
class NullByteCommandSerializer implements CommandSerializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize($command)
    {
        $serializedCommand = [];
        foreach ((array) $command as $key => $value) {
            $serializedCommand[str_replace("\0", '-', $key)] = $value;
        }

        return $serializedCommand;
    }
}
