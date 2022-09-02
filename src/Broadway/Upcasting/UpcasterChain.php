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

interface UpcasterChain
{
    /**
     * @param mixed $event the original event fetched from event store
     *
     * @return mixed the upcasted event
     */
    public function upcast($event);
}
