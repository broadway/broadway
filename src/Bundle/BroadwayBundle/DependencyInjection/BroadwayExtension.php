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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');
        $loader->load('saga.xml');
        $loader->load('read_model.xml');
        $loader->load('event_store.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $this->loadSagaStateRepository($config['saga'], $container);
        $this->loadReadModelRepository($config['read_model'], $container);
        $this->loadCommandBus($config['command_handling'], $container);
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

    private function loadSagaStateRepository(array $config, ContainerBuilder $container)
    {
        switch ($config['repository']) {
            case 'mongodb':
                $container->setAlias(
                    'broadway.saga.state.repository',
                    'broadway.saga.state.mongodb_repository'
                );
                break;
            case 'in_memory':
                $container->setAlias(
                    'broadway.saga.state.repository',
                    'broadway.saga.state.in_memory_repository'
                );
                break;
        }
    }

    private function loadReadModelRepository(array $config, ContainerBuilder $container)
    {
        switch ($config['repository']) {
            case 'elasticsearch':
                $this->configElasticsearch($config['elasticsearch'], $container);
                break;
            case 'in_memory':
                $this->configInMemory($container);
                break;
        }
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
