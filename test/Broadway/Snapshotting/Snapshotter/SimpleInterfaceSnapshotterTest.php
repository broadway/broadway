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

namespace Broadway\Snapshotting\Snapshotter;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Snapshotting\Snapshot;
use Broadway\Snapshotting\Snapshotable;
use Broadway\Snapshotting\Snapshotter;

class SimpleInterfaceSnapshotterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Snapshotter
     */
    private $snapshotter;

    /**
     * @test
     * @expectedException \Broadway\Snapshotting\SnapshottingException
     * @expectedExceptionMessage Object 'Broadway\Snapshotting\Snapshotter\TestNonSnapshotableAggregate' does not implement Snapshotable
     */
    public function it_throws_an_exception_if_aggregate_does_not_implement_Snapshotable()
    {
        $this->snapshotter->takeSnapshot(new TestNonSnapshotableAggregate());
    }

    /**
     * @test
     */
    public function it_creates_Snapshot_of_Snapshotable_aggregate()
    {

        $aggregate = new TestSnapshotableAggregate();
        $aggregate->apply(new \stdClass());

        $this->assertEquals(
            new Snapshot(
                42,
                0,
                [
                    'foo' => 'bar',
                    'bar' => ['foo', 'bar'],
                    'baz' => ['foo' => 'bar'],
                    'foobar'
                ]
            ),
            $this->snapshotter->takeSnapshot($aggregate)
        );
    }

    protected function setUp()
    {
        $this->snapshotter = new SimpleInterfaceSnapshotter();
    }
}

final class TestNonSnapshotableAggregate extends EventSourcedAggregateRoot
{

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return 42;
    }
}

final class TestSnapshotableAggregate extends EventSourcedAggregateRoot implements Snapshotable
{

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return 42;
    }

    /**
     * @return array
     */
    public function getSnapshotPayload()
    {
        return [
            'foo' => 'bar',
            'bar' => ['foo', 'bar'],
            'baz' => ['foo' => 'bar'],
            'foobar'
        ];
    }

    /**
     * @param Snapshot $snapshot
     */
    public function applySnapshot(Snapshot $snapshot)
    {
        // NO-OP
    }
}
