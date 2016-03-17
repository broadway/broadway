<?php

namespace Broadway\Bundle\BroadwayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterSerializersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach (array('metadata', 'payload', 'readmodel') as $serializer) {
            $id = $container->getParameter(sprintf('broadway.serializer.%s.service_id', $serializer));

            if (! $container->hasDefinition($id)) {
                throw new \InvalidArgumentException(sprintf(
                    'Serializer with service id "%s" could not be found',
                    $id
                ));
            }

            $container->setAlias(
                sprintf('broadway.serializer.%s', $serializer),
                $container->getParameter(sprintf('broadway.serializer.%s.service_id', $serializer))
            );
        }
    }
}
