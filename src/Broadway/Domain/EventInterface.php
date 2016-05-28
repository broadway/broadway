<?php

namespace Broadway\Domain;

interface EventInterface
{
    /**
     * @return string
     */
    public function getType();
}
