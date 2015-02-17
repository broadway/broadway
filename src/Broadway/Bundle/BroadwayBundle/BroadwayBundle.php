<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle;

use Broadway\Bundle\BroadwayBundle\Command\SchemaEventStoreCreateCommand;
use Broadway\Bundle\BroadwayBundle\Command\SchemaEventStoreDropCommand;
use Broadway\Bundle\BroadwayBundle\DependencyInjection\DefineDBALEventStoreConnectionCompilerPass;
use Broadway\Bundle\BroadwayBundle\DependencyInjection\RegisterBusSubscribersCompilerPass;
use Broadway\Bundle\BroadwayBundle\DependencyInjection\RegisterEventListenerCompilerPass;
use Broadway\Bundle\BroadwayBundle\DependencyInjection\RegisterMetadataEnricherSubscriberPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BroadwayBundle.
 */
class BroadwayBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new RegisterBusSubscribersCompilerPass(
                'broadway.command_handling.command_bus',
                'command_handler',
                'Broadway\CommandHandling\CommandHandlerInterface'
            )
        );
        $container->addCompilerPass(
            new RegisterBusSubscribersCompilerPass(
                'broadway.event_handling.event_bus',
                'broadway.domain.event_listener',
                'Broadway\EventHandling\EventListenerInterface'
            )
        );
        $container->addCompilerPass(
            new RegisterEventListenerCompilerPass(
                'broadway.event_dispatcher',
                'broadway.event_listener'
            )
        );
        $container->addCompilerPass(
            new RegisterMetadataEnricherSubscriberPass(
                'broadway.metadata_enriching_event_stream_decorator',
                'broadway.metadata_enricher'
            )
        );
        $container->addCompilerPass(
            new DefineDBALEventStoreConnectionCompilerPass($this->getContainerExtension()->getAlias())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        $application->add(new SchemaEventStoreCreateCommand());
        $application->add(new SchemaEventStoreDropCommand());
    }
}
