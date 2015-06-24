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

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to define the dbal event store connection according to the configuration.
 */
class DefineDBALEventStoreConnectionCompilerPass implements CompilerPassInterface
{
    /**
     * Validates the DBAL event store connection configuration.
     *
     * This validation needs to run in a compiler pass, because it depends
     * on DBAL services, which aren't available during the config merge.
     */
    public function process(ContainerBuilder $container)
    {
        $container->getExtension('broadway')->defineDBALEventStoreConnection($container);
    }
}
