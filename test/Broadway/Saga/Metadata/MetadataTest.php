<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\Metadata;

use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata as DomainMetadata;
use Broadway\TestCase;

class StaticallyConfiguredSagaMetadataTest extends TestCase
{
    private $metadata;

    public function setUp()
    {
        $this->metadata = new Metadata(array(
            'StaticallyConfiguredSagaMetadataTestSagaTestEvent1' => function ($event, $domainMessage) {
                self::assertNotNull($event);
                self::assertInstanceOf('Broadway\Domain\DomainMessage', $domainMessage);

                return 'criteria';
            },
        ));
    }

    /**
     * @test
     */
    public function it_handles_an_event_if_its_specified_by_the_saga()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent1();

        $domainMessage = $this->createDomainMessageForEvent($event);
        $this->assertTrue($this->metadata->handles($domainMessage));
    }

    /**
     * @test
     */
    public function it_does_not_handle_an_event_if_its_not_specified_by_the_saga()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent2();

        $domainMessage = $this->createDomainMessageForEvent($event);
        $this->assertFalse($this->metadata->handles($domainMessage));
    }

    /**
     * @test
     */
    public function it_returns_the_criteria_for_a_configured_event()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent1();

        $domainMessage = $this->createDomainMessageForEvent($event);
        $this->assertEquals('criteria', $this->metadata->criteria($domainMessage));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_throws_an_exception_if_there_is_no_criteria_for_a_given_event()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent2();

        $domainMessage = $this->createDomainMessageForEvent($event);
        $this->metadata->criteria($domainMessage);
    }

    private function createDomainMessageForEvent($event)
    {
        return DomainMessage::recordNow(1, 0, new DomainMetadata(array()), $event);
    }
}

class StaticallyConfiguredSagaMetadataTestSagaTestEvent1
{
}
class StaticallyConfiguredSagaMetadataTestSagaTestEvent2
{
}
