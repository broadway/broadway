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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Dependency injection extension.
 */
class BroadwayExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $loader->load('services.xml');
        $loader->load('saga.xml');
        $loader->load('event_store.xml');

        $this->loadSagaStateRepository($config['saga'], $container, $loader);
        $this->loadReadModelRepository($config['read_model'], $container, $loader);
        $this->loadCommandBus($config['command_handling'], $container);
        $this->loadEventStore($config['event_store'], $container);
    }

    private function loadCommandBus(array $config, ContainerBuilder $container)
    {
        if ($logger = $config['logger']) {
            $container->setAlias(
                'broadway.command_handling.command_bus',
                'broadway.command_handling.event_dispatching_command_bus'
            );

            $container->getDefinition('broadway.auditing.command_logger')
                ->replaceArgument(0, new Reference($logger));
        } else {
            $container->setAlias(
                'broadway.command_handling.command_bus',
                'broadway.command_handling.simple_command_bus'
            );
        }
    }

    private function loadSagaStateRepository(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        switch ($config['repository']) {
            case 'mongodb':
                $loader->load('saga/mongodb.xml');
                $container->setAlias(
                    'broadway.saga.state.repository',
                    'broadway.saga.state.mongodb_repository'
                );

                $database = 'broadway_%kernel.environment%%broadway.saga.mongodb.storage_suffix%';

                if (isset($config['mongodb']['connection'])) {

                    if (isset($config['mongodb']['connection']['database'])) {
                        $database = $config['mongodb']['connection']['database'];
                    }

                    $mongoConnection = $container->getDefinition('broadway.saga.state.mongodb_connection');

                    if (isset($config['mongodb']['connection']['dsn'])) {
                        $mongoConnection->replaceArgument(0, $config['mongodb']['connection']['dsn']);
                    }

                    if (isset($config['mongodb']['connection']['options'])) {
                        $mongoConnection->replaceArgument(1, $config['mongodb']['connection']['options']);
                    }
                }

                $container->setParameter('broadway.saga.mongodb.storage_suffix', (string) $config['mongodb']['storage_suffix']);
                $container->setParameter('broadway.saga.mongodb.database', $database);
                break;
            case 'in_memory':
                $loader->load('saga/in_memory.xml');
                $container->setAlias(
                    'broadway.saga.state.repository',
                    'broadway.saga.state.in_memory_repository'
                );
                break;
        }
    }

    private function loadReadModelRepository(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        switch ($config['repository']) {
            case 'elasticsearch':
                $loader->load('read_model/elasticsearch.xml');
                $this->configElasticsearch($config['elasticsearch'], $container);
                break;
            case 'in_memory':
                $loader->load('read_model/in_memory.xml');
                $this->configInMemory($container);
                break;
        }
    }

    private function loadEventStore(array $config, ContainerBuilder $container)
    {
        $container->setParameter(
            'broadway.event_store.dbal.connection',
            $config['dbal']['connection']
        );

        $container->setParameter(
            'broadway.event_store.dbal.table',
            $config['dbal']['table']
        );

        $container->setParameter(
            'broadway.event_store.dbal.use_binary',
            $config['dbal']['use_binary']
        );
    }

    private function configElasticsearch(array $config, ContainerBuilder $container)
    {
        $definition = $container->findDefinition('broadway.elasticsearch.client');

        $definition->setArguments(array(
             $config
        ));

        $container->setAlias(
            'broadway.read_model.repository_factory',
            'broadway.read_model.elasticsearch.repository_factory'
        );
    }

    private function configInMemory(ContainerBuilder $container)
    {
        $container->setAlias(
            'broadway.read_model.repository_factory',
            'broadway.read_model.in_memory.repository_factory'
        );
    }
}
