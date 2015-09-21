<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Processor;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function it_passes_the_event_and_domain_message()
    {
        $testProcessor = new TestProcessor();
        $testEvent     = new TestEvent();

        $this->assertFalse($testProcessor->isCalled());

        $testProcessor->handle($this->createDomainMessage($testEvent));

        $this->assertTrue($testProcessor->isCalled());
    }

    private function createDomainMessage($event)
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), $event);
    }
}

class TestProcessor extends Processor
{
    private $isCalled = false;

    public function handleTestEvent($event, DomainMessage $domainMessage)
    {
        $this->isCalled = true;
    }

    public function isCalled()
    {
        return $this->isCalled;
    }
}

class TestEvent
{
}
