<?php

namespace Broadway\EventStore\DBAL\Criteria;

final class NotEquals extends DBALCriteria
{
    private $propertyName;
    private $expression;

    public function __construct(DBALProperty $property, $expression)
    {
        $this->propertyName = $property;
        $this->expression = $expression;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($entryKey, &$whereClause, ParameterRegistry $parameters)
    {
        $this->propertyName->parse($entryKey, $whereClause);
        if (is_null($this->expression)) {
            $whereClause .= ' IS NOT NULL';
        } else {
            $whereClause .= ' <> ';
            if ($this->expression instanceof DBALProperty) {
                $this->expression->parse($entryKey, $whereClause);
            } else {
                $whereClause .= $parameters->register($this->expression);
            }
        }
    }
}
