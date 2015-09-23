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
use Broadway\Saga\State\Criteria;

interface MetadataInterface
{
    /**
     * @param DomainMessage $domainMessage
     *
     * @return bool True, if the saga can handle the event
     */
    public function handles(DomainMessage $domainMessage);

    /**
     * @param DomainMessage $domainMessage
     *
     * @return Criteria Criteria for the given event
     */
    public function criteria(DomainMessage $domainMessage);
}
