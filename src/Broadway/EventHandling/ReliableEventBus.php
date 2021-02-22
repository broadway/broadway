<?php

declare(strict_types=1);

namespace Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Psr\Log\LoggerInterface;

class ReliableEventBus implements EventBus
{
    /** @var array */
    private $eventListeners = [];

    /** @var array */
    private $queue = [];

    /** @var bool */
    private $isPublishing = false;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
                    $this->handleMessages($domainMessage);
                }
            } finally {
                $this->isPublishing = false;
            }
        }
    }

    private function handleMessages(DomainMessage $domainMessage): void
    {
        foreach ($this->eventListeners as $eventListener) {
            try {
                $eventListener->handle($domainMessage);
            } catch (\Throwable $exception) {
                $this->logger->error(sprintf('[Event LISTENER]: %s, failed with message %s', get_class($eventListener), $exception->getMessage()), [
                    'exception' => $exception
                ]);
            }
        }
    }
}
