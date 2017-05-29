<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting\Snapshotter;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Snapshotting\Snapshot;
use Broadway\Snapshotting\Snapshotable;
use Broadway\Snapshotting\Snapshotter;
use Broadway\Snapshotting\SnapshottingException;

final class SimpleInterfaceSnapshotter implements Snapshotter
{
    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     */
    public function takeSnapshot(EventSourcedAggregateRoot $aggregateRoot)
    {
        if (! $aggregateRoot instanceof Snapshotable) {
            throw new SnapshottingException(
                sprintf("Object '%s' does not implement Snapshotable.", get_class($aggregateRoot))
            );
        }

        return new Snapshot(
            $aggregateRoot->getAggregateRootId(),
            $aggregateRoot->getPlayhead(),
            $aggregateRoot->getSnapshotPayload()
        );
    }

}
