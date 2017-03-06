<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class SimpleEventBusTest extends TestCase
{
    private $eventBus;

    public function setUp()
    {
        $this->eventBus = new SimpleEventBus();
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

        try {
            $this->eventBus->publish($domainEventStream1);
        } catch (\Exception $e) {
            $this->assertEquals('I failed.', $e->getMessage());
        }

        $this->eventBus->publish($domainEventStream2);
    }

    private function createEventListenerMock()
    {
        return $this->getMockBuilder('Broadway\EventHandling\EventListener')->getMock();
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
        $this->eventBus          = $eventBus;
        $this->publishableStream = $publishableStream;
    }

    public function handle(DomainMessage $domainMessage)
    {
        if (! $this->handled) {
            $this->eventBus->publish($this->publishableStream);
            $this->handled = true;
        }
    }
}
