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

use Broadway\Domain\DomainEventStreamInterface;
use Exception;

/**
 * Simple synchronous publishing of events.
 */
class SimpleEventBus implements EventBusInterface
{
    private $eventListeners = [];
    private $queue          = [];
    private $isPublishing   = false;

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventListenerInterface $eventListener)
    {
        $this->eventListeners[] = $eventListener;
    }

    /**
     * {@inheritDoc}
     */
    public function publish(DomainEventStreamInterface $domainMessages)
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

                $this->isPublishing = false;
            } catch (Exception $e) {
                $this->isPublishing = false;
                throw $e;
            }
        }
    }
}
