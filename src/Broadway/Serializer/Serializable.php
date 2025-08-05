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

namespace Broadway\Serializer;

/**
 * Contract for objects serializable by the SimpleInterfaceSerializer.
 *
 * @template T of object
 * @phpstan-type SerializableData array<string, mixed>
 *
 */
interface Serializable
{
    /**
     * @param SerializableData $data
     *
     * @phpstan-return T
     */
    public static function deserialize(array $data): object;

    /**
     * @return SerializableData
     */
    public function serialize(): array;
}
