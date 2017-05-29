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

use Broadway\Snapshotting\Testing\SnapshotStoreTestCase;

class InMemorySnapshotStoreTest extends SnapshotStoreTestCase
{
    /**
     * @return SnapshotStore
     */
    protected function createStore()
    {
        return new InMemorySnapshotStore();
    }
}
