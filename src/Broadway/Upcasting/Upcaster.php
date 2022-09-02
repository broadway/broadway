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

/**
 * @template I
 * @template O
 */
interface Upcaster
{
    /**
     * @param I $event the original event fetched from event store
     */
    public function supports($event): bool;

    /**
     * @param I $event the original event fetched from event store
     *
     * @return O the upcasted event
     */
    public function upcast($event);
}
