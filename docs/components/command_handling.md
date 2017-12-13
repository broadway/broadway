CommandHandling Component
=========================

Primitives to use commands in your application.

## Command bus

An interface and two simple implementations of a command bus where commands can
be dispatched on.

## Command handler

An interface and convenient base class that command handlers can extend.

The base class provided by this component uses a convention to find out whether
the command handler can execute a command or not. To signal that your command
handler can handle a command `ExampleCommand`, just implement the
`handleExampleCommand` method in the extending class.

```php
use Broadway\CommandHandling\SimpleCommandHandler;

/**
 * A command handler that only handles ExampleCommand commands.
 */
class ExampleCommandHandler extends SimpleCommandHandler
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
```

## Testing

A helper to implement scenario based tests for command handlers that use an
event store.

## Example

The [`examples/`][examples] directory at the root of the project contains a
runnable command handling example (`command-handling`). The code you find there
contains comments with what is happening.

[examples]: ../../../examples/
