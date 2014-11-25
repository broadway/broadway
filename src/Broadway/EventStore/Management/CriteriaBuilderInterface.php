<?php

namespace Broadway\EventStore\Management;


interface CriteriaBuilderInterface
{
    /**
     * Returns a property instance that can be used to build criteria. The given <code>propertyName</code> must hold a
     * valid value for the Event Store that returns that value. Typically, it requires "indexed" values to be used,
     * such as event identifier, aggregate identifier, timestamp, etc.
     *
     * @param string $propertyName
     * @return PropertyInterface
     */
    public function property($propertyName);
}
