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

namespace MicroModule\Broadway\ReadModel;

/**
 * Represent a repository that can transfer its data to another repository.
 */
interface Transferable
{
    public function transferTo(Repository $otherRepository): void;
}
