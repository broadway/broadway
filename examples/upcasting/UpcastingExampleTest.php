<?php

declare(strict_types=1);

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\Upcasting\SequentialUpcasterChain;
use Broadway\Upcasting\UpcastingEventStore;
use Ramsey\Uuid\Uuid;

require_once __DIR__.'/Users.php';
require_once __DIR__.'/UserCreated.php';
require_once __DIR__.'/UserCreatedV2.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/UserCreatedUpcasterV1toV2.php';
require_once __DIR__.'/UserCreatedUpcasterV2toV3.php';

class UpcastingExampleTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var SimpleEventBus
     */
    private $eventBus;

    protected function setUp(): void
    {
        $this->eventBus = new SimpleEventBus();
    }

    public function it_should_do_nothing_if_there_are_no_new_versions(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingEventStore(
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
            new UserCreatedV3($userId, 'matiux', 'xuitam', 36, 'Italy')
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
     *
     * @testdox It should upcast UserCreatedV1 to UserCreateV3 when only v1 stored
     */
    public function it_should_upcast_user_created_v1_to_user_created_v3_when_only_v1_stored(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingEventStore(
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
     *
     * @testdox It should upcast UserCreatedV1 to UserCreateV3 when v1 and v2 are stored
     */
    public function it_should_upcast_user_created_v1_to_user_created_v3_when_v1_and_v2_are_stored(): void
    {
        $userId = Uuid::uuid4()->toString();

        $eventStore = new UpcastingEventStore(
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
