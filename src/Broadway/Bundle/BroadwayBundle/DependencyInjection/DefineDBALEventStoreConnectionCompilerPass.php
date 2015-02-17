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

/**
 * Compiler pass to define the dbal event store connection according to the configuration.
 */
class DefineDBALEventStoreConnectionCompilerPass implements CompilerPassInterface
{
    private $bundleAlias;

    /**
     * @param string $bundleAlias
     */
    public function __construct($bundleAlias)
    {
        $this->bundleAlias = $bundleAlias;
    }

    /**
     * Validates the DBAL event store connection configuration.
     *
     * This validation needs to run in a compiler pass, because it depends
     * on DBAL services, which aren't available during the config merge.
     */
    public function process(ContainerBuilder $container)
    {
        $connectionName = $container->getParameter('broadway.event_store.dbal.connection') ?: 'database_connection';

        $connectionServiceName = sprintf('doctrine.dbal.%s_connection', $connectionName);
        if (! $container->hasDefinition($connectionServiceName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid %s config: DBAL connection "%s" not found',
                    $this->bundleAlias,
                    $connectionName
                )
            );
        }

        $container->setAlias('broadway.event_store.dbal.connection', $connectionServiceName);
    }
}
