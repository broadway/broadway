<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore\Management;

use Broadway\EventStore\EventVisitorInterface;

interface EventStoreManagementInterface
{
    public function visitEvents(Criteria $criteria, EventVisitorInterface $eventVisitor);
}
