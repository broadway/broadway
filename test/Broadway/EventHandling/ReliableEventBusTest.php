<?php

declare(strict_types=1);

namespace Vonq\Webshop\Tests\Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListener;
use Broadway\EventHandling\ReliableEventBus;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReliableEventBusTest extends TestCase
{
    /**
     * @var ReliableEventBus
     */
    private $eventBus;

    /** @var LoggerInterface */
    private $logger;

    /** @var TestHandler */
    private $testHandler;

    protected function setUp(): void
    {
        $this->logger = new Logger('main');
        $this->testHandler = new TestHandler();
        $this->logger->pushHandler($this->testHandler);

        $this->eventBus = new ReliableEventBus($this->logger);
    }

    /**
     * @test
     */
    public function it_should_process_the_next_handle_when_a_handler_fails()
    {
        $domainMessage = $this->createDomainMessage([]);

        $domainEventStream = new DomainEventStream([$domainMessage]);

        $eventListener1 = $this->createEventListenerMock();
        $eventListener1
            ->expects($this->at(0))
            ->method('handle')
            ->willThrowException(new \Exception());

        $eventListener2 = $this->prophesize(EventListener::class);
        $eventListener2->handle($domainMessage)->shouldBeCalledOnce();

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2->reveal());
        $this->eventBus->publish($domainEventStream);

        $this->assertTrue($this->testHandler->hasErrorThatContains(sprintf('[Event LISTENER]: %s', get_class($eventListener1))));
    }

    /**
     * @test
     */
    public function it_subscribes_an_event_listener()
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

    /**
     * @test
     */
    public function it_publishes_events_to_subscribed_event_listeners()
    {
        $domainMessage1 = $this->createDomainMessage([]);
        $domainMessage2 = $this->createDomainMessage([]);

        $domainEventStream = new DomainEventStream([$domainMessage1, $domainMessage2]);

        $eventListener1 = $this->createEventListenerMock();
        $eventListener1
            ->expects($this->at(0))
            ->method('handle')
            ->with($domainMessage1);
        $eventListener1
            ->expects($this->at(1))
            ->method('handle')
            ->with($domainMessage2);

        $eventListener2 = $this->createEventListenerMock();
        $eventListener2
            ->expects($this->at(0))
            ->method('handle')
            ->with($domainMessage1);
        $eventListener2
            ->expects($this->at(1))
            ->method('handle')
            ->with($domainMessage2);

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2);
        $this->eventBus->publish($domainEventStream);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_new_events_before_all_listeners_have_run()
    {
        $domainMessage1 = $this->createDomainMessage(['foo' => 'bar']);
        $domainMessage2 = $this->createDomainMessage(['foo' => 'bas']);

        $domainEventStream = new DomainEventStream([$domainMessage1]);

        $eventListener1 = new SimpleEventBusTestListener($this->eventBus, new DomainEventStream([$domainMessage2]));

        $eventListener2 = $this->createEventListenerMock();
        $eventListener2
            ->expects($this->at(0))
            ->method('handle')
            ->with($domainMessage1);
        $eventListener2
            ->expects($this->at(1))
            ->method('handle')
            ->with($domainMessage2);

        $this->eventBus->subscribe($eventListener1);
        $this->eventBus->subscribe($eventListener2);
        $this->eventBus->publish($domainEventStream);
    }

    /**
     * @test
     */
    public function it_should_still_publish_events_after_exception()
    {
        $domainMessage1 = $this->createDomainMessage(['foo' => 'bar']);
        $domainMessage2 = $this->createDomainMessage(['foo' => 'bas']);

        $domainEventStream1 = new DomainEventStream([$domainMessage1]);
        $domainEventStream2 = new DomainEventStream([$domainMessage2]);

        $eventListener = $this->createEventListenerMock();
        $eventListener
            ->expects($this->at(0))
            ->method('handle')
            ->with($domainMessage1)
            ->will($this->throwException(new \Exception('I failed.')));

        $eventListener
            ->expects($this->at(1))
            ->method('handle')
            ->with($domainMessage2);

        $this->eventBus->subscribe($eventListener);

        $this->eventBus->publish($domainEventStream1);
        $this->eventBus->publish($domainEventStream2);
    }

    private function createEventListenerMock(): MockObject
    {
        return $this->createMock(EventListener::class);
    }

    private function createDomainMessage($payload)
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), new SimpleEventBusTestEvent($payload));
    }
}

class SimpleEventBusTestEvent
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}

class SimpleEventBusTestListener implements EventListener
{
    private $eventBus;
    private $handled = false;
    private $publishableStream;

    public function __construct($eventBus, $publishableStream)
    {
        $this->eventBus = $eventBus;
        $this->publishableStream = $publishableStream;
    }

    public function handle(DomainMessage $domainMessage): void
    {
        if (!$this->handled) {
            $this->eventBus->publish($this->publishableStream);
            $this->handled = true;
        }
    }
}
