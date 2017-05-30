<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting;

class InMemorySnapshotStore implements SnapshotStore
{
    private $snapshots = [];

    /**
     * @param Snapshot $snapshot
     */
    public function save(Snapshot $snapshot)
    {
        $this->snapshots[(string)$snapshot->getId()] = $snapshot;
    }

    /**
     * @param mixed $id
     *
     * @return Snapshot|null
     */
    public function load($id)
    {
        return ! isset($this->snapshots[(string)$id]) ? null : $this->snapshots[(string)$id];
    }
}
