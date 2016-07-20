<?php

namespace Broadway\Bundle\BroadwayBundle\DependencyInjection;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSagaCompilerPassTest extends PHPUnit_Framework_TestCase
{
    private $compilerPass;
    private $definition;

    public function setUp()
    {
        $this->compilerPass = new RegisterSagaCompilerPass('acme.saga_manager', 'acme.saga');

        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'findTaggedServiceIds', 'findDefinition'])
            ->getMock();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function it_will_throw_for_invalid_service()
    {
        $this->container->expects($this->at(0))
            ->method('has')
            ->with('acme.saga_manager')
            ->will($this->returnValue(false));

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function it_replaces_the_sagas_in_the_saga_manager()
    {
        $this->mockDefinition();

        $this->container->expects($this->once())
            ->method('findtaggedserviceids')
            ->with('acme.saga')
            ->will($this->returnvalue([
                'acme.sample_saga' => ['acme.saga' => ['type' => 'my_saga']]
            ]));

        $this->definition->expects($this->once())
            ->method('replaceArgument')
            ->with(1, ['my_saga' => new Reference('acme.sample_saga')])
            ->will($this->returnSelf());

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function it_doesnt_register_the_manager_when_no_sagas_registered()
    {
        $this->mockDefinition();

        $this->container->expects($this->once())
            ->method('findtaggedserviceids')
            ->with('acme.saga')
            ->will($this->returnvalue([]));

        $this->definition->expects($this->never())
            ->method('replaceArgument');

        $this->definition->expects($this->never())
            ->method('addTag');

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function it_adds_the_domain_listener_tag()
    {
        $this->mockDefinition();

        $this->container->expects($this->once())
            ->method('findtaggedserviceids')
            ->with('acme.saga')
            ->will($this->returnvalue([
                'acme.sample_saga' => ['acme.saga' => ['type' => 'my_saga']]
            ]));

        $this->definition->expects($this->once())
            ->method('replaceArgument')
            ->will($this->returnSelf());

        $this->definition->expects($this->once())
            ->method('addTag')
            ->with('broadway.domain.event_listener');

        $this->compilerPass->process($this->container);
    }

    private function mockDefinition()
    {
        $this->container->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));

        $this->definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->any())
            ->method('findDefinition')
            ->will($this->returnValue($this->definition));
    }
}
