<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Domain;

/**
 * Adds the ability to record itself to a Domain Message
 */
interface RecorderInterface
{
    /**
     * @param string   $id
     * @param int      $playhead
     * @param Metadata $metadata
     * @param mixed    $payload
     *
     * @return DomainMessage
     */
    public static function recordNow($id, $playhead, Metadata $metadata, $payload);
}
