<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
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

    /**
     * {@inheritdoc}
     */
    public function subscribe(EventListener $eventListener)
    {
        $this->eventBus->subscribe($eventListener);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(DomainEventStream $domainMessages)
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
     * @return array Payloads of the recorded events
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
    public function trace()
    {
        $this->tracing = true;
    }
}
