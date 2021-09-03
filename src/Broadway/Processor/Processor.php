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

namespace MicroModule\Broadway\Processor;

use MicroModule\Broadway\Domain\DomainMessage;
use MicroModule\Broadway\EventHandling\EventListener;

/**
 * Base class for event stream processors.
 */
abstract class Processor implements EventListener
{
    /**
     * {@inheritdoc}
     */
    public function handle(DomainMessage $domainMessage): void
    {
        $event = $domainMessage->getPayload();
        $method = $this->getHandleMethod($event);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($event, $domainMessage);
    }

    /**
     * @param mixed $event
     */
    private function getHandleMethod($event): string
    {
        $classParts = explode('\\', get_class($event));

        return 'handle'.end($classParts);
    }
}
