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
use Broadway\Domain\RepresentsDomainChange;
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
        $domainMessage = $this->createDomainMessage(array('foo' => 'bar'));

        $eventListener = $this->createEventListenerMock();
        $eventListener
            ->expects($this->once())
            ->method('handle')
            ->with($domainMessage);

        $this->eventBus->subscribe($eventListener);

        $this->eventBus->publish(new DomainEventStream(array($domainMessage)));
    }

    /**
     * @test
     */
    public function it_publishes_events_to_subscribed_event_listeners()
    {
        $domainMessage1 = $this->createDomainMessage(array());
        $domainMessage2 = $this->createDomainMessage(array());

        $domainEventStream = new DomainEventStream(array($domainMessage1, $domainMessage2));

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
        $domainMessage1 = $this->createDomainMessage(array('foo' => 'bar'));
        $domainMessage2 = $this->createDomainMessage(array('foo' => 'bas'));

        $domainEventStream = new DomainEventStream(array($domainMessage1));

        $eventListener1 = new SimpleEventBusTestListener($this->eventBus, new DomainEventStream(array($domainMessage2)));

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

    private function createEventListenerMock()
    {
        return $this->getMockBuilder('Broadway\EventHandling\ListensForEvents')->getMock();
    }

    private function createDomainMessage($payload)
    {
        return DomainMessage::recordNow(1, 1, new Metadata(array()), new SimpleEventBusTestEvent($payload));
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

class SimpleEventBusTestListener implements ListensForEvents
{
    private $eventBus;
    private $handled = false;
    private $publishableStream;

    public function __construct($eventBus, $publishableStream)
    {
        $this->eventBus          = $eventBus;
        $this->publishableStream = $publishableStream;
    }

    public function handle(RepresentsDomainChange $domainMessage)
    {
        if (! $this->handled) {
            $this->eventBus->publish($this->publishableStream);
            $this->handled = true;
        }
    }
}
