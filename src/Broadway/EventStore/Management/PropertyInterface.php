<?php

namespace Broadway\EventStore\Management;

interface PropertyInterface
{
    /**
     * Returns a criteria instance where the property must be "less than" the given <code>expression</code>. Some event
     * stores also allow the given expression to be a property.
     *
     * @param mixed expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "less than" requirement.
     */
    public function lessThan($expression);

    /**
     * Returns a criteria instance where the property must be "less than" or "equal to" the given
     * <code>expression</code>. Some event stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "less than or equals" requirement.
     */
    public function lessThanEquals($expression);

    /**
     * Returns a criteria instance where the property must be "greater than" the given <code>expression</code>. Some
     * event stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "greater than" requirement.
     */
    public function greaterThan($expression);

    /**
     * Returns a criteria instance where the property must be "greater than" or "equal to" the given
     * <code>expression</code>. Some event stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "greater than or equals" requirement.
     */
    public function greaterThanEquals($expression);

    /**
     * Returns a criteria instance where the property must "equal" the given <code>expression</code>. Some event stores
     * also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing an "equals" requirement.
     */
    public function is($expression);

    /**
     * Returns a criteria instance where the property must be "not equal to" the given <code>expression</code>. Some
     * event stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "not equals" requirement.
     */
    public function isNot($expression);

    /**
     * Returns a criteria instance where the property must be "in" the given <code>expression</code>. Some event stores
     * also allow the given expression to be a property.
     * <p/>
     * Note that the given <code>expression</code> must describe a collection of some sort.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "is in" requirement.
     */
    public function in($expression);

    /**
     * Returns a criteria instance where the property must be "not in" the given <code>expression</code>. Some event
     * stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "is not in" requirement.
     */
    public function notIn($expression);

    /**
     * Returns a criteria instance where the property must begin with the given <code>expression</code>. Some event
     * stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "begins with" requirement.
     */
    public function beginsWith($expression);

    /**
     * Returns a criteria instance where the property must end with the given <code>expression</code>. Some event
     * stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "ends with" requirement.
     */
    public function endsWith($expression);

    /**
     * Returns a criteria instance where the property must contain the given <code>expression</code>. Some event
     * stores also allow the given expression to be a property.
     *
     * @param mixed $expression The expression to match against the property
     * @return CriteriaInterface a criteria instance describing a "contains" requirement.
     */
    public function contains($expression);
}
