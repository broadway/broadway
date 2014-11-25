<?php

namespace Broadway\EventStore\Management;

interface CriteriaInterface
{
    /**
     * Returns a criteria instance where both <code>this</code> and given <code>criteria</code> must match.
     *
     * @param CriteriaInterface $criteria The criteria that must match
     * @return CriteriaInterface a criteria instance that matches if both <code>this</code> and <code>criteria</code> match
     */
    public function andWith(CriteriaInterface $criteria);

    /**
     * Returns a criteria instance where either <code>this</code> or the given <code>criteria</code> must match.
     *
     * @param CriteriaInterface $criteria criteria that must match if <code>this</code> doesn't match
     * @return CriteriaInterface a criteria instance that matches if <code>this</code> or the given <code>criteria</code> match
     */
    public function orWith(CriteriaInterface $criteria);
}
