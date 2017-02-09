<?php
namespace Broadway\EventSourcing\IdempotentCommands;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\TestCase;

class IdempotentCommandStreamDecoratorTest extends TestCase
{
    const COMMAND_ID_META_KEY = 'command-id';

    /** @var IdempotentCommandStreamDecorator */
    private $decorator;
    private $commandId;
    /** @var EventStoreInterface */
    private $eventStore;

    protected function setUp()
    {
        $this->commandId = uniqid();

        $this->eventStore = new InMemoryEventStore();
        $this->eventStore->append('id', $this->createDomainEventStream(1));

        $this->decorator = new IdempotentCommandStreamDecorator(
            $this->eventStore, $this->commandId, self::COMMAND_ID_META_KEY);
    }

    /** @test */
    public function it_adds_command_id_as_metadata()
    {
        $eventStream    = $this->createDomainEventStream(2);
        $newEventStream = $this->decorator->decorateForWrite('type', 'id', $eventStream);

        $expectedMetadata = new Metadata([self::COMMAND_ID_META_KEY => $this->commandId, 'bar' => 1337]);

        /** @var DomainMessage $domainMessage */
        foreach ($newEventStream as $domainMessage) {
            $metadata = $domainMessage->getMetadata();

            $this->assertEquals($expectedMetadata, $metadata);
        }
    }

    /**
     * @test
     * @expectedException \Broadway\EventSourcing\IdempotentCommands\DuplicateCommandException
     */
    public function it_throws_exception_when_same_command_has_been_executed()
    {
        $eventStream    = $this->createDomainEventStream(2);
        $newEventStream = $this->decorator->decorateForWrite('type', 'id', $eventStream);
        $this->eventStore->append('id', $newEventStream);

        $otherEventStream = $this->createDomainEventStream(3);
        $this->decorator->decorateForWrite('type', 'id', $otherEventStream);
    }

    private function createDomainEventStream($playhead)
    {
        $m1 = DomainMessage::recordNow('id', $playhead, Metadata::kv('bar', 1337), 'payload');

        return new DomainEventStream([$m1]);
    }
}
