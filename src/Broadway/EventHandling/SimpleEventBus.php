<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;

/**
 * Simple synchronous publishing of events.
 */
class SimpleEventBus implements EventBus
{
    private $eventListeners = [];
    private $queue          = [];
    private $isPublishing   = false;

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventListener $eventListener)
    {
        $this->eventListeners[] = $eventListener;
    }

    /**
     * {@inheritDoc}
     */
    public function publish(DomainEventStream $domainMessages)
    {
        foreach ($domainMessages as $domainMessage) {
            $this->queue[] = $domainMessage;
        }

        if (! $this->isPublishing) {
            $this->isPublishing = true;

            try {
                while ($domainMessage = array_shift($this->queue)) {
                    foreach ($this->eventListeners as $eventListener) {
                        $eventListener->handle($domainMessage);
                    }
                }
            } finally {
                $this->isPublishing = false;
            }
        }
    }
}
