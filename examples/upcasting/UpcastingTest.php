<?php

declare(strict_types=1);

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\Upcasting\SequentialUpcasterChain;
use Broadway\Upcasting\UpcastingInMemoryEventStore;
use Ramsey\Uuid\Uuid;

require_once __DIR__.'/Users.php';
require_once __DIR__.'/UserCreated.php';
require_once __DIR__.'/UserCreatedV2.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/UserCreatedUpcasterV1toV2.php';
require_once __DIR__.'/UserCreatedUpcasterV2toV3.php';

class UpcastingTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var SimpleEventBus
     */
    private $eventBus;

    protected function setUp(): void
    {
        $this->eventBus = new SimpleEventBus();
    }

    /**
     * @test
     */
    public function it_should_do_nothing_if_there_are_no_new_versions(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingInMemoryEventStore(
            new InMemoryEventStore(),
            new SequentialUpcasterChain([
                new UserCreatedUpcasterV1toV2(),
                new UserCreatedUpcasterV2toV3(),
            ])
        );

        $events[] = DomainMessage::recordNow(
            $userId,
            0,
            new Metadata([]),
            new UserCreatedV3($userId, 'matiux', 'xuitam', 36,'Italy')
        );

        $eventStore->append($userId, new DomainEventStream($events));

        $users = new Users($eventStore, $this->eventBus);

        $matiux = $users->load($userId);

        self::assertInstanceOf(User::class, $matiux);
        self::assertEquals('matiux xuitam of age: 36', $matiux->name());
        self::assertEquals('Italy', $matiux->country());
    }


    /**
     * @test
     */
    public function it_should_upcast_UserCreatedV1_event_to_UserCreatedV3_with_default_values(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingInMemoryEventStore(
            new InMemoryEventStore(),
            new SequentialUpcasterChain([
                new UserCreatedUpcasterV1toV2(),
                new UserCreatedUpcasterV2toV3(),
            ])
        );

        $events[] = DomainMessage::recordNow(
            $userId,
            0,
            new Metadata([]),
            new UserCreated($userId, 'matiux')
        );

        $eventStore->append($userId, new DomainEventStream($events));

        $users = new Users($eventStore, $this->eventBus);

        $matiux = $users->load($userId);

        self::assertInstanceOf(User::class, $matiux);
        self::assertEquals('matiux N/A of age: -1', $matiux->name());
        self::assertEquals('N/A', $matiux->country());
    }

    /**
     * @test
     */
    public function it_should_upcast_UserCreatedV1_event_to_UserCreatedV3_passing_by_from_v2_with_default_values(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingInMemoryEventStore(
            new InMemoryEventStore(),
            new SequentialUpcasterChain([
                new UserCreatedUpcasterV1toV2(),
                new UserCreatedUpcasterV2toV3(),
            ])
        );

        $events[] = DomainMessage::recordNow(
            $userId,
            0,
            new Metadata([]),
            new UserCreated($userId, 'matiux')
        );

        $events[] = DomainMessage::recordNow(
            $userId,
            1,
            new Metadata([]),
            new UserCreatedV2($userId, 'matiux', 'xuitam', 'Italy')
        );

        $eventStore->append($userId, new DomainEventStream($events));

        $users = new Users($eventStore, $this->eventBus);

        $matiux = $users->load($userId);

        self::assertInstanceOf(User::class, $matiux);
        self::assertEquals('matiux xuitam of age: -1', $matiux->name());
        self::assertEquals('Italy', $matiux->country());
    }
}
