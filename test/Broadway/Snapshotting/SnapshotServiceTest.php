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
use Broadway\Snapshotting\Snapshotter\SimpleInterfaceSnapshotter;

class SnapshotServiceTest extends \PHPUnit_Framework_TestCase
{
    private $snapshotService;
    private $snapshotStore;

    /**
     * @test
     * @expectedException \Broadway\Snapshotting\SnapshotNotFoundException
     */
    public function it_propagates_SnapshotNotFoundException_when_no_snapshot_not_found()
    {
        $this->snapshotStore
            ->load(42)
            ->shouldBeCalled()
            ->willThrow(new SnapshotNotFoundException());

        $this->snapshotService->load(42);
    }

    /**
     * @test
     */
    public function it_can_save_Snapshot_in_SnapshotStore()
    {
        $aggregate = $this->createAggregateWithEvents(5);

        $this->snapshotStore
            ->save(new Snapshot(42, 4, ['foo' => 'bar']))
            ->shouldBeCalled();

        $this->snapshotService->save($aggregate);
    }

    protected function setUp()
    {
        $this->snapshotStore = $this->prophesize(SnapshotStore::class);
        $this->snapshotService = new SnapshotService(
            $this->snapshotStore->reveal(),
            new SimpleInterfaceSnapshotter()
        );
    }

    private function createAggregateWithEvents($numberOfEvents)
    {
        $aggregate = new TestSnapshotAggregate();
        for ($i = 0; $i < $numberOfEvents; $i++) {
            $aggregate->apply(new \stdClass());
        }

        return $aggregate;
    }
}

final class TestSnapshotAggregate extends EventSourcedAggregateRoot implements Snapshotable
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
        ];
    }
}
