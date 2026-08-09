<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventDispatcher;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    private CallableEventDispatcher $dispatcher;

    private object $listener1;

    private object $listener2;

    protected function setUp(): void
    {
        $this->dispatcher = new CallableEventDispatcher();
        $this->listener1 = $this->getTraceableEventListener();
        $this->listener2 = $this->getTraceableEventListener();

        $this->assertFalse($this->listener1->isCalled);
        $this->assertFalse($this->listener2->isCalled);
    }

    #[Test]
    public function it_calls_the_subscribed_listeners(): void
    {
        $this->dispatcher->addListener('event', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event', [$this->listener2, 'handleEvent']);

        $this->dispatcher->dispatch('event', ['value1', 'value2']);

        $this->assertTrue($this->listener1->isCalled);
        $this->assertTrue($this->listener2->isCalled);
    }

    #[Test]
    public function it_only_calls_the_listener_subscribed_to_a_given_event(): void
    {
        $this->dispatcher->addListener('event1', [$this->listener1, 'handleEvent']);
        $this->dispatcher->addListener('event2', [$this->listener2, 'handleEvent']);

        $this->dispatcher->dispatch('event1', ['value1', 'value2']);

        $this->assertTrue($this->listener1->isCalled);
        $this->assertFalse($this->listener2->isCalled);
    }

    private function getTraceableEventListener(): object
    {
        return new class {
            public bool $isCalled = false;

            public function handleEvent($value1, $value2): void
            {
                $this->isCalled = true;
            }
        };
    }
}
