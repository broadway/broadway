<?php

namespace Broadway\EventStore\DBAL\Criteria;

use Broadway\EventStore\Management\expression;
use Broadway\EventStore\Management\PropertyInterface;

class DBALProperty implements PropertyInterface
{
    /** @var string */
    private $propertyName;

    public function __construct($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    public function lessThan($expression)
    {
        return new SimpleOperator($this, "<", $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function lessThanEquals($expression)
    {
        return new SimpleOperator($this, "<=", $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function greaterThan($expression)
    {
        return new SimpleOperator($this, ">", $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function greaterThanEquals($expression)
    {
        return new SimpleOperator($this, ">=", $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function is($expression)
    {
        return new Equals($this, $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function isNot($expression)
    {
        return new NotEquals($this, $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function in($expression)
    {
        return new CollectionOperator($this, 'IN', $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function notIn($expression)
    {
        return new CollectionOperator($this, 'NOT IN', $expression);
    }

    /**
     * {@inheritDoc}
     */
    public function beginsWith($expression)
    {
        $expression = $this->escapeLikeExpression($expression);
        return new SimpleOperator($this, 'LIKE', "$expression%");
    }

    /**
     * {@inheritDoc}
     */
    public function endsWith($expression)
    {
        $expression = $this->escapeLikeExpression($expression);
        return new SimpleOperator($this, 'LIKE', "%$expression");
    }

    /**
     * {@inheritDoc}
     */
    public function contains($expression)
    {
        $expression = $this->escapeLikeExpression($expression);
        return new SimpleOperator($this, 'LIKE', "%$expression%");
    }

    /*
     * Escape a LIKE expression
     * @todo is this good enough
     */
    private function escapeLikeExpression($expression)
    {
        return addcslashes($expression, '_%');
    }


    /**
     * Parse the property value to a valid EJQL expression.
     *
     * @param string $entryKey The variable assigned to the entry holding the property
     * @param string $stringBuilder The builder to append the expression to
     */
    public function parse($entryKey, &$stringBuilder)
    {
        if ($entryKey != null && strlen($entryKey) > 0) {
            $stringBuilder .= $entryKey . '.';
        }
        $stringBuilder .= $this->propertyName;
    }
}
