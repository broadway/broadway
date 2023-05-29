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
 * Serializes commands to an array of scalars.
 */
interface CommandSerializer
{
    /**
     * Serializes the command.
     */
    public function serialize($command): array;
}
