<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel;

/**
 * Represent a repository that can transfer its data to another repository.
 */
interface Transferable
{
    public function transferTo(Repository $otherRepository);
}
