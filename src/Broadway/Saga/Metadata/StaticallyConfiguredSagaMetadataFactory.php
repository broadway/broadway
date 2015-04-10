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

use RuntimeException;

class StaticallyConfiguredSagaMetadataFactory implements MetadataFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create($saga)
    {
        $requiredInterface = 'Broadway\Saga\Metadata\StaticallyConfiguredSagaInterface';

        if (! is_subclass_of($saga, $requiredInterface)) {
            throw new RuntimeException(
                sprintf('Provided saga of class %s must implement %s', get_class($saga), $requiredInterface)
            );
        }

        $criteria = $saga::configuration();

        return new Metadata($criteria);
    }
}
