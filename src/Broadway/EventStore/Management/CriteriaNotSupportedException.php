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

/**
 * Criteria not supported by implementation
 *
 * In some cases an event store implementation may implement management
 * but not be able to satisfy all criteria options. In this case, the
 * implementation must throw this exception.
 *
 * Class CriteriaNotSupportedException
 * @package Broadway\EventStore\Management
 */
class CriteriaNotSupportedException extends EventStoreManagementException
{
}
