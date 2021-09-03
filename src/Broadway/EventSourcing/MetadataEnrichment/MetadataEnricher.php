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

namespace MicroModule\Broadway\EventSourcing\MetadataEnrichment;

use MicroModule\Broadway\Domain\Metadata;

/**
 * Adds extra metadata to already existing metadata.
 */
interface MetadataEnricher
{
    public function enrich(Metadata $metadata): Metadata;
}
