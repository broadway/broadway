<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel;

use Broadway\Domain\RepresentsDomainChange;
use Broadway\EventHandling\ListensForEvents;

/**
 * Handles events and projects to a read model.
 */
abstract class Projector implements ListensForEvents
{
    /**
     * {@inheritDoc}
     */
    public function handle(RepresentsDomainChange $domainMessage)
    {
        $event  = $domainMessage->getPayload();
        $method = $this->getHandleMethod($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->$method($event, $domainMessage);
    }

    private function getHandleMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}
