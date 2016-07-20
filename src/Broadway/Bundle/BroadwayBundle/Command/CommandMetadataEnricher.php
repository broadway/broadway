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

use Broadway\Domain\Metadata;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnricherInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Enricher that adds information about the excecuted console command.
 */
class CommandMetadataEnricher implements MetadataEnricherInterface
{
    private $event;

    /**
     * {@inheritDoc}
     */
    public function enrich(Metadata $metadata)
    {
        if (null === $this->event) {
            return $metadata;
        }

        $data = [
            'console' => [
                'command'   => get_class($this->event->getCommand()),
                'arguments' => $this->event->getInput()->__toString()
            ]
        ];
        $newMetadata = new Metadata($data);

        return $metadata->merge($newMetadata);
    }

    public function handleConsoleCommandEvent(ConsoleCommandEvent $event)
    {
        $this->event = $event;
    }
}
