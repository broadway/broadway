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

use Broadway\TestCase;

class StaticallyConfiguredSagaMetadataTest extends TestCase
{
    private $metadata;

    public function setUp()
    {
        $this->metadata = new Metadata(array(
            'StaticallyConfiguredSagaMetadataTestSagaTestEvent1' => function () { return 'criteria'; },
        ));
    }

    /**
     * @test
     */
    public function it_handles_an_event_if_its_specified_by_the_saga()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent1();

        $this->assertTrue($this->metadata->handles($event));
    }

    /**
     * @test
     */
    public function it_does_not_handle_an_event_if_its_not_specified_by_the_saga()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent2();

        $this->assertFalse($this->metadata->handles($event));
    }

    /**
     * @test
     */
    public function it_returns_the_criteria_for_a_configured_event()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent1();

        $this->assertEquals('criteria', $this->metadata->criteria($event));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_throws_an_exception_if_there_is_no_criteria_for_a_given_event()
    {
        $event = new StaticallyConfiguredSagaMetadataTestSagaTestEvent2();

        $this->metadata->criteria($event);
    }
}

class StaticallyConfiguredSagaMetadataTestSagaTestEvent1
{
}
class StaticallyConfiguredSagaMetadataTestSagaTestEvent2
{
}
