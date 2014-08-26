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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                    ->children()
                        ->scalarNode('logger')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('saga')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('repository')
                            ->values(array('in_memory', 'mongodb'))
                            ->defaultValue('mongodb')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('read_model')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('repository')
                            ->values(array('in_memory', 'elasticsearch'))
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
                                        return array($v);
                                    })
                                ->end()
                                ->defaultValue(array('localhost:9200'))
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
