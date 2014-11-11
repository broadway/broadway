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

use Broadway\Saga\State\Criteria;
use Broadway\TestCase;

class StaticallyConfiguredSagaMetadataFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_metadata_using_the_saga_configuration()
    {
        $this->markTestSkipped('Yay phpunit');
        $metadataFactory = new StaticallyConfiguredSagaMetadataFactory();
        $criteria        = new Criteria(array('id' => 'YoLo'));

        $saga = $this->getMockBuilder('Broadway\Saga\Metadata\StaticallyConfiguredSagaInterface')->getMock();
        $saga->staticExpects($this->any())
            ->method('configuration')
            ->will($this->returnValue(array('StaticallyConfiguredSagaMetadataFactoryTestEvent' => function ($event) use ($criteria) { return $criteria;})));

        $metadata = $metadataFactory->create($saga);

        $this->assertInstanceOf('Broadway\Saga\MetadataInterface', $metadata);

        $event = new StaticallyConfiguredSagaMetadataFactoryTestEvent();
        $this->assertTrue($metadata->handles($event));
        $this->assertEquals($criteria, $metadata->criteria($event));
    }
}

class StaticallyConfiguredSagaMetadataFactoryTestEvent
{
}
