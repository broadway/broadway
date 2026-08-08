<?php

declare(strict_types=1);

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReliableEventBusTest extends TestCase
{
    private ReliableEventBus $eventBus;
    private LoggerInterface $logger;
    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->logger = new Logger('main');
        $this->testHandler = new TestHandler();
        $this->logger->pushHandler($this->testHandler);

        $this->eventBus = new ReliableEventBus($this->logger);
    }

    #[Test]
    public function it_should_process_the_next_handle_when_a_handler_fails(): void
    {
        $domainMessage = $this->createDomainMessage([]);

        $domainEventStream = new DomainEventStream([$domainMessage]);

        $eventListener1 = $this->createEventListenerMock();
        $eventListener1
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new \Exception());

        $eventListener2 = $this->createMock(EventListener::class);
        $eventListener2
            ->expects($this->once())
            ->method('handle')
            ->with($domainMessage)
        ;

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2);
        $this->eventBus->publish($domainEventStream);

        $this->assertTrue($this->testHandler->hasErrorThatContains(sprintf('[Event LISTENER]: %s', get_class($eventListener1))));
    }

    #[Test]
    public function it_subscribes_an_event_listener(): void
    {
        $domainMessage = $this->createDomainMessage(['foo' => 'bar']);

        $eventListener = $this->createEventListenerMock();
        $eventListener
            ->expects($this->once())
            ->method('handle')
            ->with($domainMessage);

        $this->eventBus->subscribe($eventListener);

        $this->eventBus->publish(new DomainEventStream([$domainMessage]));
    }

    #[Test]
    public function it_publishes_events_to_subscribed_event_listeners(): void
    {
        $domainMessage1 = $this->createDomainMessage([]);
        $domainMessage2 = $this->createDomainMessage([]);

        $domainEventStream = new DomainEventStream([$domainMessage1, $domainMessage2]);

        $matcher = $this->exactly(2);
        $eventListener1 = $this->createEventListenerMock();
        $eventListener1
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                fn (DomainMessage $message) => match ($matcher->numberOfInvocations()) {
                    1 => $message === $domainMessage1,
                    2 => $message === $domainMessage2,
                    default => throw new \Exception('Unexpected message'),
                }
            );

        $matcher = $this->exactly(2);
        $eventListener2 = $this->createEventListenerMock();
        $eventListener2
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                fn (DomainMessage $message) => match ($matcher->numberOfInvocations()) {
                    1 => $message === $domainMessage1,
                    2 => $message === $domainMessage2,
                    default => throw new \Exception('Unexpected message'),
                }
            );

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2);
        $this->eventBus->publish($domainEventStream);
    }

    #[Test]
    public function it_does_not_dispatch_new_events_before_all_listeners_have_run(): void
    {
        $domainMessage1 = $this->createDomainMessage(['foo' => 'bar']);
        $domainMessage2 = $this->createDomainMessage(['foo' => 'bas']);

        $domainEventStream = new DomainEventStream([$domainMessage1]);

        $eventListener1 = new class($this->eventBus, new DomainEventStream([$domainMessage2])) implements EventListener {
            public bool $handled = false;

            public function __construct(
                public readonly EventBus $eventBus,
                public readonly DomainEventStream $publishableStream,
            ) {
            }

            public function handle(DomainMessage $domainMessage): void
            {
                if (!$this->handled) {
                    $this->eventBus->publish($this->publishableStream);
                    $this->handled = true;
                }
            }
        };

        $matcher = $this->exactly(2);
        $eventListener2 = $this->createEventListenerMock();
        $eventListener2
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                fn (DomainMessage $message) => match ($matcher->numberOfInvocations()) {
                    1 => $message === $domainMessage1,
                    2 => $message === $domainMessage2,
                    default => throw new \Exception('Unexpected message'),
                }
            );

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2);
        $this->eventBus->publish($domainEventStream);
    }

    #[Test]
    public function it_should_still_publish_events_after_exception(): void
    {
        $domainMessage1 = $this->createDomainMessage(['foo' => 'bar']);
        $domainMessage2 = $this->createDomainMessage(['foo' => 'bas']);

        $domainEventStream1 = new DomainEventStream([$domainMessage1]);
        $domainEventStream2 = new DomainEventStream([$domainMessage2]);

        $matcher = $this->exactly(2);
        $eventListener = $this->createEventListenerMock();
        $eventListener
            ->expects($matcher)
            ->method('handle')
            ->willReturnCallback(
                fn (DomainMessage $message) => match ($matcher->numberOfInvocations()) {
                    1 => $message === $domainMessage1,
                    2 => $message === $domainMessage2,
                    default => throw new \Exception('Unexpected message'),
                }
            );

        $this->eventBus->subscribe($eventListener);

        $this->eventBus->publish($domainEventStream1);
        $this->eventBus->publish($domainEventStream2);
    }

    /**
     * @phpstan-return MockObject<EventListener>
     */
    private function createEventListenerMock(): EventListener
    {
        return $this->createMock(EventListener::class);
    }

    private function createDomainMessage(array $payload): DomainMessage
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), new class($payload) {
            public function __construct(public array $data)
            {
            }
        });
    }
}
