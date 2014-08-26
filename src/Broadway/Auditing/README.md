Auditing Component
==================

Add an audit trail to your application. Currently enables you to log whether
commands were successful or failed due to an exception.

## Usage

Register the `CommandLogger` event listener with the
`EventDispatchingCommandHandler`. The logger will use the injected logger to
log whether a command was executed successfully or errored.

## Example

The [`examples/`][examples] directory at the root of the project contains a
runnable auditing (`auditing`). The code you find there contains comments with
what is happening.

[examples]: ../../../examples/
