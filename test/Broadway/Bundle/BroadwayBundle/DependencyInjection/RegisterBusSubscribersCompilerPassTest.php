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

use Broadway\Bundle\BroadwayBundle\TestCase;
use Symfony\Component\DependencyInjection\Reference;

class RegisterBusSubscribersCompilerPassTest extends TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function command_handler_must_have_proper_interface()
    {
        // one service, not implementing any interface
        $services = array(
            'my_fake_command_handler' => array(),
        );

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $registerListenersPass = new RegisterBusSubscribersCompilerPass('bus_service', 'tag', 'Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriberInterface');
        $registerListenersPass->process($builder);
    }

    /**
     * @test
     */
    public function command_handler_should_be_subscribed()
    {
        $commandHandlerId = 'my_command_handler';
        $services = array(
            $commandHandlerId => array(),
        );

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriber'));

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));

        $commandBus = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $commandBus->expects($this->once())
            ->method('addMethodCall')
            ->with('subscribe', array(new Reference($commandHandlerId)));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $builder->expects($this->atLeastOnce())
            ->method('findDefinition')
            ->will($this->returnValue($commandBus));

        $registerListenersPass = new RegisterBusSubscribersCompilerPass('bus_service', 'tag', 'Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriberInterface');
        $registerListenersPass->process($builder);
    }
}

interface BusSubscriberInterface
{
}

class BusSubscriber implements BusSubscriberInterface
{
}
