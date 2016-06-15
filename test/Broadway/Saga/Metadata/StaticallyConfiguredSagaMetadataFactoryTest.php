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
use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use Broadway\TestCase;

class StaticallyConfiguredSagaMetadataFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_metadata_using_the_saga_configuration()
    {
        $metadataFactory = new StaticallyConfiguredSagaMetadataFactory();
        $criteria        = new Criteria(array('id' => 'YoLo'));

        StaticallyConfiguredSagaMock::setConfiguration(array(
            'StaticallyConfiguredSagaMetadataFactoryTestEvent' => function ($event) use ($criteria) {
                return $criteria;
            }
        ));
        $saga = new StaticallyConfiguredSagaMock();

        $metadata = $metadataFactory->create($saga);

        $this->assertInstanceOf('Broadway\Saga\MetadataInterface', $metadata);

        $event = new StaticallyConfiguredSagaMetadataFactoryTestEvent();
        $domainMessage = DomainMessage::recordNow(1, 0, new DomainMetadata(array()), $event);

        $this->assertTrue($metadata->handles($domainMessage));
        $this->assertEquals($criteria, $metadata->criteria($domainMessage));
    }
}

class StaticallyConfiguredSagaMetadataFactoryTestEvent
{
}

class StaticallyConfiguredSagaMock implements StaticallyConfiguredSagaInterface
{
    private static $configuration;

    public static function setConfiguration($configuration)
    {
        self::$configuration = $configuration;
    }

    public static function configuration()
    {
        return self::$configuration;
    }

    /**
     * @inheritdoc
     */
    public function handle(DomainMessage $domainMessage, State $state)
    {
        throw new \RuntimeException('I found not handle anything');
    }
}
