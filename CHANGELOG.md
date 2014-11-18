# Changelog

## v0.3.0

#### BC breaks

- The AggregateFactory is a new required constructor argument for a EventSourcingRepository and the order of the arguments changed.

#### Summary of changes

- Added Aggregate Factories for instantiating aggregates. Now we are not bound to a public constructor. 
- A bugfix that caused an infinite loop when supplying a string to the CommandHandler.
- Saga base class is now abstract
- More typehints to interfaces instead of concrete classes
- Multiple CS fixes
