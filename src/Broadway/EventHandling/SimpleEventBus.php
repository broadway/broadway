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
class SimpleEventBus implements PublishesEvents
{
    private $eventListeners = array();
    private $queue          = array();
    private $isPublishing   = false;

    /**
     * {@inheritDoc}
     */
    public function subscribe(ListensForEvents $eventListener)
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

            while ($domainMessage = array_shift($this->queue)) {
                foreach ($this->eventListeners as $eventListener) {
                    $eventListener->handle($domainMessage);
                }
            }

            $this->isPublishing = false;
        }
    }
}
