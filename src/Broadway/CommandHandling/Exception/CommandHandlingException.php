<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\CommandHandling\Exception;

use RuntimeException;

/**
 * Class CommandHandlingException
 */
class CommandHandlingException extends RuntimeException
{
    /**
     * @var \Exception
     */
    private $originalException;

    /**
     * @var array
     */
    private $incompleteCommandStack;

    /**
     * @param \Exception $originalException
     * @param array      $incompleteCommands
     */
    public function __construct(\Exception $originalException, array $incompleteCommands)
    {
        $this->originalException = $originalException;
        $this->incompleteCommandStack = $incompleteCommands;

        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);
    }

    /**
     * @return \Exception
     */
    public function getOriginalException()
    {
        return $this->originalException;
    }

    /**
     * @return array
     */
    public function getIncompleteCommandStack()
    {
        return $this->incompleteCommandStack;
    }
}
