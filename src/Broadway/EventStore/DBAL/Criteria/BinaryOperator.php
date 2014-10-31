<?php

namespace Broadway\EventStore\DBAL\Criteria;

final class BinaryOperator extends DBALCriteria
{
    private $criteria1;
    private $criteria2;
    private $operator;

    public function __construct(DBALCriteria $criteria1, $operator, DBALCriteria $criteria2)
    {
        $this->criteria1 = $criteria1;
        $this->operator = $operator;
        $this->criteria2 = $criteria2;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($entryKey, &$whereClause, ParameterRegistry $parameters)
    {
        $whereClause .= '(';
        $this->criteria1->parse($entryKey, $whereClause, $parameters);
        $whereClause .= ') '.$this->operator.' (';
        $this->criteria2->parse($entryKey, $whereClause, $parameters);
        $whereClause .= ')';
    }
}
