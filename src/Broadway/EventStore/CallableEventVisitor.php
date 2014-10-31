<?php 

namespace Broadway\EventStore;

use Broadway\Domain\DomainMessageInterface;
use InvalidArgumentException;

class CallableEventVisitor implements EventVisitorInterface
{
    private $callable;

    /**
     * @param callable $callable
     * @throws \InvalidArgumentException
     */
    public function __construct($callable)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException("First argument to new CallableEventVisitor must be callable");
        }
        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function doWithEvent(DomainMessageInterface $domainMessage)
    {
        call_user_func($this->callable, $domainMessage);
    }
}
