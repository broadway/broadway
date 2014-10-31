<?php

namespace Broadway\EventStore\DBAL\Criteria;

final class CollectionOperator extends DBALCriteria
{
    private $propertyName;
    private $expression;
    private $operator;

    public function __construct(DBALProperty $property, $operator, $expression)
    {
        $this->propertyName = $property;
        $this->operator = $operator;
        $this->expression = $expression;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($entryKey, &$whereClause, ParameterRegistry $parameters)
    {
        $this->propertyName->parse($entryKey, $whereClause);
        $whereClause .= ' '.$this->operator.' ';
        if ($this->expression instanceof DBALProperty) {
            $this->expression->parse($entryKey, $whereClause);
        } else {
            $whereClause .= '('.$parameters->register($this->expression).')';
        }
    }
}
