<?php

namespace Broadway\EventStore\DBAL\Criteria;


class ParameterRegistry
{
    private $parameters = array();

    /**
     * @param mixed $expression
     * @return string
     */
    public function register($expression)
    {
        $this->parameters[] = $expression;
        return '?';
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
