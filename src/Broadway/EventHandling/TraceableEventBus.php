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
use Broadway\Domain\DomainMessage;

/**
 * Event bus that is able to record all dispatched events.
 */
final class TraceableEventBus implements EventBus
{
    private $eventBus;
    private $recorded = [];
    private $tracing = false;

    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function subscribe(EventListener $eventListener): void
    {
        $this->eventBus->subscribe($eventListener);
    }

    public function publish(DomainEventStream $domainMessages): void
    {
        $this->eventBus->publish($domainMessages);

        if (!$this->tracing) {
            return;
        }

        foreach ($domainMessages as $domainMessage) {
            $this->recorded[] = $domainMessage;
        }
    }

    /**
     * @return mixed[] Payloads of the recorded events
     */
    public function getEvents(): array
    {
        return array_map(
            function (DomainMessage $message) {
                return $message->getPayload();
            },
            $this->recorded
        );
    }

    /**
     * Start tracing.
     */
    public function trace(): void
    {
        $this->tracing = true;
    }
}
