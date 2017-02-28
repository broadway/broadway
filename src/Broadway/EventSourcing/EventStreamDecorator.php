<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing;

use Broadway\Domain\DomainEventStreamInterface;

/**
 * Interface implemented by event stream decorators.
 *
 * An event stream decorator can alter the domain event stream before it is
 * written. An example would be adding metadata before writing the events to
 * storage.
 */
interface EventStreamDecorator
{
    /**
     * @param string                     $aggregateType
     * @param string                     $aggregateIdentifier
     * @param DomainEventStreamInterface $eventStream
     *
     * @return DomainEventStreamInterface
     */
    public function decorateForWrite($aggregateType, $aggregateIdentifier, DomainEventStreamInterface $eventStream);
}
