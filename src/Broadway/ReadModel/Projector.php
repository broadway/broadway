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

use Broadway\Domain\DomainMessage;

/**
 * Handles events and projects to a read model.
 */
abstract class Projector implements ProjectorInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event  = $domainMessage->getPayload();
        $method = $this->getHandleMethod($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->$method($event, $domainMessage);
    }

    /**
     * @param object $event
     * @return string
     */
    private function getHandleMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}
