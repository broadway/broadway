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

use PHPUnit_Framework_TestCase;

class DefineDBALEventStoreConnectionCompilerPassTest extends PHPUnit_Framework_TestCase
{
    const ALIAS = 'awesome';

    public function setUp()
    {
        $this->compilerPass = new DefineDBALEventStoreConnectionCompilerPass(self::ALIAS);

        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['hasDefinition', 'setAlias', 'hasParameter', 'getParameter'])
            ->getMock();
    }

    /**
     * @test
     */
    public function it_does_nothing_if_dbal_was_not_configured()
    {
        $this->container
            ->method('hasParameter')
            ->with('broadway.event_store.dbal.connection')
            ->will($this->returnValue(false));

        $this->container
            ->method('hasDefinition')
            ->with('doctrine.dbal.default_connection')
            ->will($this->returnValue(false)); // should not matter

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function it_aliases_the_doctrine_connection()
    {
        $this->mockConnectionParameter();
        $this->container
            ->method('hasDefinition')
            ->with('doctrine.dbal.default_connection')
            ->will($this->returnValue(true));

        $this->container
            ->method('setAlias')
            ->with('broadway.event_store.dbal.connection', 'doctrine.dbal.default_connection');

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid awesome config: DBAL connection "default" not found
     */
    public function it_throws_an_error_for_invalid_doctrine_connection()
    {
        $this->mockConnectionParameter();
        $this->container
            ->method('hasDefinition')
            ->will($this->returnValue(false));

        $this->compilerPass->process($this->container);
    }

    private function mockConnectionParameter() {
        $this->container
            ->method('hasParameter')
            ->with('broadway.event_store.dbal.connection')
            ->will($this->returnValue(true));

        $this->container
            ->method('getParameter')
            ->with('broadway.event_store.dbal.connection')
            ->will($this->returnValue('default'));

    }
}
