<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\Metadata;

use Broadway\Saga\SagaInterface;

/**
 * @todo: ?? :D
 */
interface StaticallyConfiguredSagaInterface extends SagaInterface
{
    public static function configuration();
}
