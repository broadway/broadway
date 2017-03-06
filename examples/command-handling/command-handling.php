<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * A command handler that only handles ExampleCommand commands.
 */
class ExampleCommandHandler extends Broadway\CommandHandling\SimpleCommandHandler
{
    /**
     * Method handling ExampleCommand commands.
     *
     * The fact that this method handles the ExampleCommand is signalled by the
     * convention of the method name: `handle<CommandClassName>`.
     */
    public function handleExampleCommand(ExampleCommand $command)
    {
        echo $command->getMessage() . "\n";
    }
}

/**
 * Command object.
 */
class ExampleCommand
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}

// Setup the command handler
$commandHandler = new ExampleCommandHandler();

// Create a command bus and subscribe the command handler at the command bus
$commandBus = new Broadway\CommandHandling\SimpleCommandBus();
$commandBus->subscribe($commandHandler);

// Create and dispatch the command!
$command = new ExampleCommand('Hi from command!');
$commandBus->dispatch($command);
