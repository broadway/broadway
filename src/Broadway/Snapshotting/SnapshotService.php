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

use Broadway\EventSourcing\EventSourcedAggregateRoot;

class SnapshotService
{
    private $snapshotStore;
    private $snapshotter;

    /**
     * @param SnapshotStore $snapshotStore
     * @param Snapshotter   $snapshotter
     */
    public function __construct(SnapshotStore $snapshotStore, Snapshotter $snapshotter)
    {
        $this->snapshotStore = $snapshotStore;
        $this->snapshotter = $snapshotter;
    }

    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     */
    public function save(EventSourcedAggregateRoot $aggregateRoot)
    {
        $this->snapshotStore->save(
            $this->snapshotter->takeSnapshot($aggregateRoot)
        );
    }

    /**
     * @param mixed $id
     *
     * @return Snapshot
     *
     * @throws SnapshotNotFoundException
     */
    public function load($id)
    {
        return $this->snapshotStore->load($id);
    }
}
