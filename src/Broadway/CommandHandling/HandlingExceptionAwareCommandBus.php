<?php
/**
 * @author Thomas Ploch <thomas.ploch@meinfernbus.de>
 */

namespace Broadway\CommandHandling;

use Broadway\CommandHandling\Exception\CommandHandlingException;

/**
 * Class HandlingExceptionAwareCommandBus
 */
class HandlingExceptionAwareCommandBus implements CommandBusInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @inheritDoc
     */
    public function dispatch($command)
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (CommandHandlingException $handlingException) {
            throw $handlingException->getOriginalException();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function subscribe(CommandHandlerInterface $handler)
    {
        $this->commandBus->subscribe($handler);
    }
}
