<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventDispatcher;

use Broadway\TestCase;

class EventDispatcherTest extends TestCase
{
    private $dispatcher;
    private $listener1;
    private $listener2;

    public function setUp()
    {
        $this->dispatcher = new CallableEventDispatcher();
        $this->listener1  = new TracableEventListener();
        $this->listener2  = new TracableEventListener();

        $this->assertFalse($this->listener1->isCalled());
        $this->assertFalse($this->listener2->isCalled());
    }

    /**
     * @test
     */
    public function it_calls_the_subscribed_listeners()
    {
        $this->dispatcher->addListener('event', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event', [$this->listener2, 'handleEvent']);

        $this->dispatcher->dispatch('event', ['value1', 'value2']);

        $this->assertTrue($this->listener1->isCalled());
        $this->assertTrue($this->listener2->isCalled());
    }

    /**
     * @test
     */
    public function it_only_calls_the_listener_subscribed_to_a_given_event()
    {
        $this->dispatcher->addListener('event1', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event2', [$this->listener2, 'handleEvent']);

        $this->dispatcher->dispatch('event1', ['value1', 'value2']);

        $this->assertTrue($this->listener1->isCalled());
        $this->assertFalse($this->listener2->isCalled());
    }
}

class TracableEventListener
{
    private $isCalled = false;

    public function isCalled()
    {
        return $this->isCalled;
    }

    public function handleEvent($value1, $value2)
    {
        $this->isCalled = true;
    }
}
