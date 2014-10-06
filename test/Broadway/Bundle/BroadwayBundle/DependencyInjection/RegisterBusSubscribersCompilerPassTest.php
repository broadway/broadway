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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    public function setUp()
    {
        $this->builder    = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->commandBus = $this->getMock('Symfony\Component\DependencyInjection\Definition');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "my_fake_command_handler" must implement interface "Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriberInterface"
     */
    public function command_handler_must_have_proper_interface()
    {
        // one service, not implementing any interface
        $services = array(
            'my_fake_command_handler' => array(),
        );

        $this->expects_bus_definition();
        $this->expects_tagged_services_and_returns($services);
        $this->expects_class_from_definition_and_returns('stdClass');

        $registerListenersPass = new RegisterBusSubscribersCompilerPass('bus_service', 'tag', 'Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriberInterface');
        $registerListenersPass->process($this->builder);
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

        $this->expects_bus_definition();
        $this->expects_tagged_services_and_returns($services);
        $this->expects_class_from_definition_and_returns('Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriber');
        $this->expects_subscribe_call_on_bus($commandHandlerId);

        $registerListenersPass = new RegisterBusSubscribersCompilerPass('bus_service', 'tag', 'Broadway\Bundle\BroadwayBundle\DependencyInjection\BusSubscriberInterface');
        $registerListenersPass->process($this->builder);
    }

    /**
     * @param $taggedServiceClass
     */
    private function expects_class_from_definition_and_returns($taggedServiceClass)
    {
        $parameterBag = $this->getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');
        $this->builder->expects($this->atLeastOnce())
            ->method('getParameterBag')
            ->will($this->returnValue($parameterBag));

        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue($taggedServiceClass));

        $this->builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $parameterBag->expects($this->atLeastOnce())
            ->method('resolveValue')
            ->will($this->returnValue($taggedServiceClass));
    }

    private function expects_bus_definition()
    {
        $this->builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $this->builder->expects($this->atLeastOnce())
            ->method('findDefinition')
            ->will($this->returnValue($this->commandBus));
    }

    private function expects_tagged_services_and_returns($services)
    {
        $this->builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));
    }

    private function expects_subscribe_call_on_bus($commandHandlerId)
    {
        $this->commandBus->expects($this->once())
            ->method('addMethodCall')
            ->with('subscribe', array(new Reference($commandHandlerId)));
    }
}

interface BusSubscriberInterface
{
}

class BusSubscriber implements BusSubscriberInterface
{
}
