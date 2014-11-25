<?php

namespace Broadway\EventStore\DBAL\Criteria;

use Broadway\EventStore\Management\CriteriaBuilderInterface;

class DBALCriteriaBuilder implements CriteriaBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function property($propertyName)
    {
        return new DBALProperty($propertyName);
    }
}
