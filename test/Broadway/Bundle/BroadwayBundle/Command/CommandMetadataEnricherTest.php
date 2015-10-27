<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle\Command;

use Broadway\Bundle\BroadwayBundle\TestCase;
use Broadway\Domain\Metadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandMetadataEnricherTest extends TestCase
{
    private $command;
    private $input;
    private $event;
    private $enricher;
    private $metadata;

    public function setUp()
    {
        $this->command   = new Command();
        $this->arguments = 'broadway:test:command argument --option=true --env=dev';

        $this->input = $this->getMockBuilder('Symfony\Component\Console\Input\ArgvInput')
            ->disableOriginalConstructor()
            ->getMock();
        $this->input->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($this->arguments));

        $output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event    = new ConsoleCommandEvent($this->command, $this->input, $output);
        $this->enricher = new CommandMetadataEnricher();
        $this->metadata = new Metadata(['yolo' => 'bam']);
    }

    /**
     * @test
     */
    public function it_adds_the_command_class_and_arguments()
    {
        $this->enricher->handleConsoleCommandEvent($this->event);

        $expected = $this->metadata->merge(new Metadata([
            'console' => [
                'command'   => 'Broadway\Bundle\BroadwayBundle\Command\Command',
                'arguments' => $this->arguments
            ]
        ]));

        $actual = $this->enricher->enrich($this->metadata);
        $this->assertEquals($expected, $actual);
    }
}

class Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('broadway:test:command')
            ->setDescription('This is a test command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return;
    }
}
