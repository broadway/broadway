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

/**
 * Command serializer that uses php hacks to get the data from a command.
 *
 * There are many other ways to implement serialization on commands, but since
 * this is only for logging purposes we get away with this solution for now.
 */
final class NullByteCommandSerializer implements CommandSerializer
{
    public function serialize($command): array
    {
        $serializedCommand = [];
        foreach ((array) $command as $key => $value) {
            $serializedCommand[str_replace("\0", '-', $key)] = $value;
        }

        return $serializedCommand;
    }
}
