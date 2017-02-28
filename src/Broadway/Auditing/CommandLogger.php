<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Auditing;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * Logs whether commands where executed successfully or whether they failed.
 *
 * This object can be registered as an event listener.
 */
class CommandLogger
{
    private $logger;
    private $commandSerializer;

    public function __construct(LoggerInterface $logger, CommandSerializer $commandSerializer)
    {
        $this->logger            = $logger;
        $this->commandSerializer = $commandSerializer;
    }

    /**
     * @param mixed $command Command that was executed successfully
     */
    public function onCommandHandlingSuccess($command)
    {
        $messageData = [
            'status'  => 'success',
            'command' => $this->getCommandData($command)
        ];

        $this->logger->info(json_encode($messageData));
    }

    /**
     * @param mixed     $command   Command that errored
     * @param Exception $exception Exception that occured during the execution of the command
     */
    public function onCommandHandlingFailure($command, Exception $exception)
    {
        $messageData = [
            'status'    => 'failure',
            'command'   => $this->getCommandData($command),
            'exception' => [
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'class'   => get_class($exception),
                'line'    => $exception->getLine(),
                'code'    => $exception->getCode()
            ]
        ];

        $this->logger->info(json_encode($messageData));
    }

    private function getCommandData($command)
    {
        return [
            'class' => get_class($command),
            'data'  => $this->commandSerializer->serialize($command),
        ];
    }
}
