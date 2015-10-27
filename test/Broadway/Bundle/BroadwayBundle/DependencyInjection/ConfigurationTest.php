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

use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ConfigurationTestCase;

class ConfigurationTest extends ConfigurationTestCase
{
    /**
     * @test
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage is not allowed for path "broadway.saga.repository". Permissible values: "in_memory", "mongodb"
     */
    public function only_in_memory_and_mongodb_are_valid_state_repositories()
    {
        $configuration = $this->processConfiguration(new Configuration(), ['broadway' => ['saga' => ['repository' => 'false_name']]]);
    }

    /**
     * @test
     */
    public function it_sets_mongodb_as_default_state_repository()
    {
        $configuration = $this->processConfiguration(new Configuration(), []);
        $this->assertEquals($configuration['saga']['repository'], 'mongodb');
    }

    /**
     * @test
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage is not allowed for path "broadway.read_model.repository". Permissible values: "in_memory", "elasticsearch"
     */
    public function only_elasticsearch_and_in_memory_are_valid_readmodel_repositories()
    {
        $configuration = $this->processConfiguration(new Configuration(), ['broadway' => ['read_model' => ['repository' => 'false_name']]]);
    }

    /**
     * @test
     */
    public function it_sets_elasticsearch_as_default_repository()
    {
        $configuration = $this->processConfiguration(new Configuration(), []);
        $this->assertEquals($configuration['read_model']['repository'], 'elasticsearch');
    }

    /**
     * @test
     */
    public function it_sets_elasticsearch_default_host_to_localhost()
    {
        $configuration = $this->processConfiguration(new Configuration(), ['broadway' => ['read_model' => ['repository' => 'elasticsearch']]]);
        $this->assertEquals($configuration['read_model']['elasticsearch'], ['hosts' => ['localhost:9200']]);
    }

    /**
     * @test
     */
    public function it_sets_the_logger()
    {
        $configuration = $this->processConfiguration(new Configuration(), ['broadway' => ['command_handling' => ['logger' => 'my_service']]]);
        $this->assertEquals('my_service', $configuration['command_handling']['logger']);
    }

    /**
     * @test
     */
    public function it_defaults_logging_to_false()
    {
        $configuration = $this->processConfiguration(new Configuration(), ['broadway' => []]);
        $this->assertFalse($configuration['command_handling']['logger']);
    }
}
