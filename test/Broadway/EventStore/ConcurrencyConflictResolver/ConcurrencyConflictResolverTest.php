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

namespace MicroModule\Broadway\EventStore\ConcurrencyConflictResolver;

use MicroModule\Broadway\Domain\DateTime;
use MicroModule\Broadway\Domain\DomainMessage;
use MicroModule\Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;

abstract class ConcurrencyConflictResolverTest extends TestCase
{
    protected function createDomainMessage($id, $playhead, $event)
    {
        return new DomainMessage($id, $playhead, new Metadata([]), $event, DateTime::now());
    }
}
