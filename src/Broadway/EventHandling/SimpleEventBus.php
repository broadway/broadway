<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;

/**
 * Simple synchronous publishing of events.
 */
final class SimpleEventBus implements EventBus
{
    private $eventListeners = [];
    private $queue = [];
    private $isPublishing = false;

    public function subscribe(EventListener $eventListener): void
    {
        $this->eventListeners[] = $eventListener;
    }

    public function publish(DomainEventStream $domainMessages): void
    {
        foreach ($domainMessages as $domainMessage) {
            $this->queue[] = $domainMessage;
        }

        if (!$this->isPublishing) {
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
