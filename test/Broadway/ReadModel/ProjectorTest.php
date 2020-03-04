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

namespace Broadway\ReadModel;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;

class ProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function it_passes_the_event_and_domain_message()
    {
        $testProjector = new TestProjector();
        $testEvent = new TestEvent();

        $this->assertFalse($testProjector->isCalled());

        $testProjector->handle($this->createDomainMessage($testEvent));

        $this->assertTrue($testProjector->isCalled());
    }

    private function createDomainMessage($event)
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), $event);
    }
}

class TestProjector extends Projector
{
    private $isCalled = false;

    public function applyTestEvent($event, DomainMessage $domainMessage)
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
