<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Serializer;

/**
 * Contract for objects serializable by the SimpleInterfaceSerializer.
 */
interface Serializable
{
    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data);

    /**
     * @return array
     */
    public function serialize();
}
