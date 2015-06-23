<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle\DependencyInjection;

use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

class BroadwayExtensionTest extends ExtensionTestCase
{
    private $extension;

    public function setUp()
    {
        parent::setUp();
        $this->extension = new BroadwayExtension();
    }

    /**
     * @test
     * @dataProvider stateConfigurationToRepositoryMapping
     */
    public function saga_state_repository_set_to_configured_repository($repoType, $class)
    {
        $configuration = array('saga' => array('repository' => $repoType));

        $this->load($this->extension, $configuration);

        $this->assertDICAliasClass('broadway.saga.state.repository', $class);
    }

    public function stateConfigurationToRepositoryMapping()
    {
        return array(
            array('in_memory', 'Broadway\Saga\State\InMemoryRepository'),
            array('mongodb'  , 'Broadway\Saga\State\MongoDBRepository'),
        );
    }

    /**
     * @test
     */
    public function default_saga_state_repository_is_mongodb()
    {
        $this->load($this->extension, array());

        $this->assertDICAliasClass('broadway.saga.state.repository', 'Broadway\Saga\State\MongoDBRepository');
    }

    /**
     * @test
     */
    public function it_uses_the_configured_storage_suffix_for_mongodb_saga_storage()
    {
        $this->load($this->extension, array('saga' => array('mongodb' => array('storage_suffix' => 'foo_suffix'))));

        $this->assertTrue($this->container->hasParameter('broadway.saga.mongodb.storage_suffix'));
        $this->assertEquals('foo_suffix', $this->container->getParameter('broadway.saga.mongodb.storage_suffix'));
    }

    /**
     * @test
     */
    public function it_defaults_to_empty_string_when_no_storage_suffix_is_configured_for_saga_storage()
    {
        $this->load($this->extension, array());

        $this->assertTrue($this->container->hasParameter('broadway.saga.mongodb.storage_suffix'));
        $this->assertEquals('', $this->container->getParameter('broadway.saga.mongodb.storage_suffix'));
    }

    /**
     * @test
     */
    public function it_uses_configured_connection_details_when_using_mongo_for_saga_repositories()
    {
        $dsn = 'mongodb://12.34.45.6:27018/awesome';
        $options = array(
            'connectTimeoutMS' => 50
        );

        $this->load($this->extension, array(
            'saga' => array(
                'repository' => 'mongodb',
                'mongodb' => array(
                    'connection' => array(
                        'dsn' => $dsn,
                        'options' => $options,
                    ),
                ),
            ),
        ));

        $def = $this->container->getDefinition('broadway.saga.state.mongodb_connection');

        $this->assertEquals($dsn, $def->getArgument(0));
        $this->assertEquals($options, $def->getArgument(1));
    }

    /**
     * @test
     * @dataProvider readModelConfigurationToRepositoryMapping
     */
    public function read_model_repository_factory_set_to_configured_repository_factory($repoFactory, $class)
    {
        $configuration = array('read_model' => array('repository' => $repoFactory));

        $this->load($this->extension, $configuration);

        $this->assertDICAliasClass('broadway.read_model.repository_factory', $class);
    }

    public function readModelConfigurationToRepositoryMapping()
    {
        return array(
            array('in_memory',     'Broadway\ReadModel\InMemory\InMemoryRepositoryFactory'),
            array('elasticsearch', 'Broadway\ReadModel\ElasticSearch\ElasticSearchRepositoryFactory'),
        );
    }

    /**
     * @test
     */
    public function default_read_model_repository_factory_is_elasticsearch()
    {
        $this->load($this->extension, array());

        $this->assertDICAliasClass('broadway.read_model.repository_factory', 'Broadway\ReadModel\ElasticSearch\ElasticSearchRepositoryFactory');
    }

    /**
     * @test
     */
    public function it_enables_the_simple_command_bus()
    {
        $configuration = array('command_handling' => array('logger' => false));

        $this->load($this->extension, $configuration);
        $this->assertDICAliasClass('broadway.command_handling.command_bus', 'Broadway\CommandHandling\SimpleCommandBus');
    }

    /**
     * @test
     */
    public function it_enables_the_logging_command_bus()
    {
        $configuration = array('command_handling' => array('logger' => 'service'));

        $this->load($this->extension, $configuration);
        $this->assertDICAliasClass('broadway.command_handling.command_bus', 'Broadway\CommandHandling\EventDispatchingCommandBus');
    }

    /**
     * @test
     */
    public function it_sets_the_logger_in_the_auditing_command_logger()
    {
        $configuration = array('command_handling' => array('logger' => 'service'));

        $this->load($this->extension, $configuration);

        $loggingCommandBus = $this->container->getDefinition('broadway.auditing.command_logger');
        $actualReference   = $loggingCommandBus->getArgument(0);
        $expectedReference = new Reference('service');
        $this->assertEquals($expectedReference, $actualReference);
    }

    private function assertDICAliasClass($aliasId, $class)
    {
        $definitionId = (string) $this->container->getAlias($aliasId);
        $this->assertDICDefinitionClass($this->container->getDefinition($definitionId), $class);
    }
}
