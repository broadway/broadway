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

namespace MicroModule\Broadway\EventSourcing;

use MicroModule\Broadway\Domain\DomainEventStream;

/**
 * Interface implemented by event stream decorators.
 *
 * An event stream decorator can alter the domain event stream before it is
 * written. An example would be adding metadata before writing the events to
 * storage.
 */
interface EventStreamDecorator
{
    public function decorateForWrite(string $aggregateType, string $aggregateIdentifier, DomainEventStream $eventStream): DomainEventStream;
}
