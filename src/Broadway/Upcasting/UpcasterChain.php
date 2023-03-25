<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2022 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Upcasting;

use Broadway\Domain\DomainMessage;

interface UpcasterChain
{
    public function upcast(DomainMessage $domainMessage): DomainMessage;
}
