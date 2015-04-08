# Changelog

## v0.6.x

#### BC breaks

- The Scenario for CommandHandling now clears the recorded events after each `then`. So for each then you only need to supply the **newly** recorded events.

## v0.5.x

#### BC breaks

- DomainMessageInterface has been removed, and DomainMessage has been made final.
- Renamed `add` method to `save` for [aggregate root repositories](https://github.com/mbadolato/broadway/commit/9b07dfc4998d70b4c6d25dcacf114a60ea7f1450).

##### Bundle

- The global `storage_suffix` parameter has been removed and has been replaced with a configuration value: `saga.mongodb.storage_suffix`.

#### Summary of other changes

- New example on how to use child entities.
- The EventSourcing Scenario has been updated to support all the latest changes.
- An AggregateRootScenarioTestCase has been added with an example on how to use it.
- The command bus and event bus have been made more resilient.
- We now publish the decorated event stream on the event bus.
- Added possibility to use binary as UUID column. See README in the Bundle for configuration details.
- The CLI Command in the Bundle doesn't throw errors anymore if the schema already exists.

## v0.4.x

#### BC breaks

- Updated `beberlei/assert` requirement to 2.0.

## v0.3.0

#### BC breaks

- The AggregateFactory is a new required constructor argument for a EventSourcingRepository and the order of the arguments changed.

#### Summary of changes

- Added Aggregate Factories for instantiating aggregates. Now we are not bound to a public constructor.
- A bugfix that caused an infinite loop when supplying a string to the CommandHandler.
- Saga base class is now abstract.
- More typehints to interfaces instead of concrete classes.
- Multiple CS fixes.
