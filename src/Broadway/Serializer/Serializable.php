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

namespace MicroModule\Broadway\Serializer;

/**
 * Contract for objects serializable by the SimpleInterfaceSerializer.
 */
interface Serializable
{
    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data);

    public function serialize(): array;
}
