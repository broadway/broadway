<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *
 */

namespace Broadway\Snapshotting;

class Snapshot
{
    private $id;
    private $playhead;
    private $payload;

    /**
     * @param mixed $id
     * @param int   $playhead
     * @param array $payload
     */
    public function __construct($id, $playhead, array $payload)
    {
        $this->id = $id;
        $this->playhead = $playhead;
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPlayhead()
    {
        return $this->playhead;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

}
