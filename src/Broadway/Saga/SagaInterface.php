<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga;

use Broadway\Domain\DomainMessage;

interface SagaInterface
{
    /**
     * @param DomainMessage $domainMessage
     * @param State $state
     *
     * @return State
     */
    public function handle(DomainMessage $domainMessage, State $state);
}
