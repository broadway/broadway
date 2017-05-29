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

interface Snapshotter
{
    /**
     * @param EventSourcedAggregateRoot $aggregateRoot
     *
     * @return Snapshot
     *
     * @throws SnapshottingException
     */
    public function takeSnapshot(EventSourcedAggregateRoot $aggregateRoot);
}
