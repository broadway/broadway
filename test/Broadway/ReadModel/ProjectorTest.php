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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProjectorTest extends TestCase
{
    #[Test]
    public function it_passes_the_event_and_domain_message(): void
    {
        $testProjector = new TestProjector();
        $testEvent = new TestEvent();

        $this->assertFalse($testProjector->isCalled);

        $testProjector->handle($this->createDomainMessage($testEvent));

        $this->assertTrue($testProjector->isCalled);
    }

    private function createDomainMessage($event): DomainMessage
    {
        return DomainMessage::recordNow(1, 1, new Metadata([]), $event);
    }
}

final class TestProjector extends Projector
{
    public bool $isCalled = false;

    public function applyTestEvent($event, DomainMessage $domainMessage)
    {
        $this->isCalled = true;
    }
}

final readonly class TestEvent
{
}
