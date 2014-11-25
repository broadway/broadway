<?php

namespace Broadway\EventStore\DBAL\Criteria;

use Broadway\EventStore\Management\CriteriaInterface;

abstract class DBALCriteria implements CriteriaInterface
{
    /**
     * Returns a criteria instance where both <code>this</code> and given <code>criteria</code> must match.
     *
     * @param CriteriaInterface $criteria The criteria that must match
     * @return CriteriaInterface a criteria instance that matches if both <code>this</code> and <code>criteria</code> match
     */
    public function andWith(CriteriaInterface $criteria)
    {
        return new BinaryOperator($this, "AND", $criteria);
    }

    /**
     * Returns a criteria instance where either <code>this</code> or the given <code>criteria</code> must match.
     *
     * @param CriteriaInterface $criteria criteria that must match if <code>this</code> doesn't match
     * @return CriteriaInterface a criteria instance that matches if <code>this</code> or the given <code>criteria</code> match
     */
    public function orWith(CriteriaInterface $criteria)
    {
        return new BinaryOperator($this, "OR", $criteria);
    }

    /**
     * Parses the criteria to a SQL compatible where clause and parameter values.
     *
     * @param string $entryKey The variable assigned to the entry in the whereClause
     * @param string $whereClause The buffer to write the where clause to.
     * @param ParameterRegistry $parameters The registry where parameters and assigned values can be registered.
     * @return void
     */
    abstract public function parse($entryKey, &$whereClause, ParameterRegistry $parameters);
}
