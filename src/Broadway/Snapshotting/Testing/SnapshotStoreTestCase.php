<?php

/**
 * This file is part of the broadway/broadway package.
 *
 *  (c) Qandidate.com <opensource@qandidate.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Broadway\Snapshotting\Testing;

use Broadway\Snapshotting\Snapshot;
use Broadway\Snapshotting\SnapshotStore;
use Broadway\TestCase;

abstract class SnapshotStoreTestCase extends TestCase
{
    /**
     * @var SnapshotStore
     */
    protected $store;

    /**
     * @test
     */
    public final function it_implements_SnapshotStore()
    {
        $this->assertInstanceOf(SnapshotStore::class, $this->store);
    }

    /**
     * @test
     * @expectedException \Broadway\Snapshotting\SnapshotNotFoundException
     */
    public final function it_throws_SnapshotNotFoundException_when_no_snapshot_found()
    {
        $this->store->load(42);
    }

    /**
     * @test
     */
    public final function it_can_retrieve_a_previously_stored_Snapshot()
    {
        $snapshot = new Snapshot(
            42,
            21,
            [
                'foo' => 'bar',
                'bar' => ['foo', 'bar'],
                'baz' => ['foo' => 'bar'],
                'foobar'
            ]
        );
        $this->store->save($snapshot);
        $this->assertEquals(
            $snapshot,
            $this->store->load(42)
        );
    }

    /**
     * @return SnapshotStore
     */
    protected abstract function createStore();

    protected function setUp()
    {
        $this->store = $this->createStore();
    }
}
