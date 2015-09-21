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

use Doctrine\DBAL\Version;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Configuration definition.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('broadway');

        $rootNode
            ->children()
                ->arrayNode('command_handling')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always(function (array $v) {
                            if (isset($v['logger']) && $v['logger']) {
                                // auditing requires event dispatching
                                $v['dispatch_events'] = true;
                            }

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->booleanNode('dispatch_events')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('logger')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('event_store')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('dbal')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultTrue()
                                ->end()
                                ->scalarNode('table')
                                    ->defaultValue('events')
                                ->end()
                                ->scalarNode('connection')
                                    ->defaultValue('default')
                                ->end()
                                ->booleanNode('use_binary')
                                    ->defaultFalse()
                                    ->validate()
                                    ->ifTrue()
                                        ->then(function ($v) {
                                            if (Version::compare('2.5.0') >= 0) {
                                                throw new InvalidConfigurationException(
                                                    'The Binary storage is only available with Doctrine DBAL >= 2.5.0'
                                                );
                                            }

                                            return $v;
                                        })
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('saga')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('repository')
                            ->values(['in_memory', 'mongodb'])
                            ->defaultValue('mongodb')
                        ->end()
                        ->arrayNode('mongodb')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('connection')
                                    ->children()
                                        ->scalarNode('dsn')->defaultNull()->end()
                                        ->scalarNode('database')->defaultNull()->end()
                                        ->arrayNode('options')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->scalarNode('storage_suffix')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('payload')->defaultValue('broadway.simple_interface_serializer')->end()
                        ->scalarNode('readmodel')->defaultValue('broadway.simple_interface_serializer')->end()
                        ->scalarNode('metadata')->defaultValue('broadway.simple_interface_serializer')->end()
                    ->end()
                ->end()
                ->arrayNode('read_model')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('repository')
                            ->values(['in_memory', 'elasticsearch'])
                            ->defaultValue('elasticsearch')
                        ->end()
                        ->arrayNode('elasticsearch')
                            ->addDefaultsIfNotSet()
                            ->children()
                            ->arrayNode('hosts')
                                ->beforeNormalization()
                                    ->ifTrue(function ($v) {
                                        return is_string($v);
                                    })
                                    ->then(function ($v) {
                                        return [$v];
                                    })
                                ->end()
                                ->defaultValue(['localhost:9200'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('connectionParams')
                                ->children()
                                    ->arrayNode('auth')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
