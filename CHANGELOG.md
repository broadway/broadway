# Changelog

## v2.0.x

#### BC breaks

* [4e6fe27](http://github.com/broadway/broadway/commit/4e6fe27eb4c35d67fc5a2cffccfca62000f9f929) Finalized concrete classes (Nikita Konstantinov)
* [5c3cc30](http://github.com/broadway/broadway/commit/5c3cc30a03ef92358745cb9bdfcae46c554195b4) required PHP 7 (othillo)
* [8ee2fd2](http://github.com/broadway/broadway/commit/8ee2fd2ebb2adcb144f9f8160e106f3b18498036) required PHPUnit 6 (Alexander Bachmann)

#### Other changes

* [f74c855](http://github.com/broadway/broadway/commit/f74c85589b9ad096a2e8bbab917fca773898cd13) Added a closure command handler (Francesco Trucchia)
* [b21c245](http://github.com/broadway/broadway/commit/b21c245f4788c62df4fa5200e2f313481521a589) Provide access to Metadata values (Reen Lokum)
* [05e88ce](http://github.com/broadway/broadway/commit/05e88ce83837bea224b65df70f9ebceebd39ed90) Moved to Symfony coding standards (Reen Lokum)
* [df69c8d](http://github.com/broadway/broadway/commit/df69c8d1996fcb9786635ef7bfadb4873c9be3cd) Added reflection serializer (Alexander Bachmann)
* [8486286](http://github.com/broadway/broadway/commit/848628664e61faaced00bc41926989c63c76e8e7) moved RepositoryTestCase, EventStoreTest, EventStoreManagementTest to Testing namespace (Robin van der Vleuten)

## v1.0.x

#### BC breaks

- The EventStore interface added a `loadFromPlayhead` method
- The ReadModelTestCase is renamed to SerializableReadModelTestCase
- We moved the Doctrine DBAL event store implementation to a [separate repository](https://github.com/broadway/event-store-dbal)
- We moved the Elasticsearch read model implementation to a [separate repository](https://github.com/broadway/read-model-elasticsearch) 
- We moved the Symfony bundle to a [separate repository](https://github.com/broadway/broadway-bundle)
- We moved the Saga component to a [separate repository](https://github.com/broadway/broadway-saga)
- DBALEventStore and InMemoryEventStore can now throw DuplicatePlayheadException.
  Ensure you are catching EventStoreException instead of specific driver exceptions.

## v0.10.x

#### Other changes

- allow specifying the DateTime used in the ReadModel Scenario
- added the ReflectionAggregateFactory as an alternative to the NamedConstructorAggregateFactory
- specify ReadModel type searching Elasticsearch read model repository
- added PHPUnit as a development dependency
- adopted new PHP 5.4 and PHP 5.5 language features (DateTimeImmutable, ::class, short array syntax)

## v0.9.x

#### BC breaks

- We raised the minimum required version of symfony/dependency-injection from 2.3 to 2.6.

#### Other changes

- The Symfony Bundle is now Symfony 3 compatible
- The DBALEventStore can now be disabled in configuration
- elasticsearch/elasticsearch-php 2.0 is now also supported
- Serializers are now configurable in the Symfony Bundle

## v0.8.x

#### BC breaks

- We raised the minimum required PHP version from 5.3 to 5.5.

#### Other changes

- Support for [querying the event store](https://github.com/qandidate-labs/broadway/commit/e81d4ea167ce97383a9a4b7d85542e8b5e11900a) using criteria
- The `COMMAND_FAILURE` event now receives [an associative array](https://github.com/qandidate-labs/broadway/blob/140d23f90259bace9601b17ebf749317cd859180/src/Broadway/CommandHandling/EventDispatchingCommandBus.php#L48) when it gets dispatched.
- Fixed a locale issue with creating DateTime objects.

## v0.7.x

#### Symfony Bundle

- You can now configure which Doctrine DBAL connection should be used for the event store
- The auditing command logger service now only gets registered when it's explicitly enabled
- You can now register Sagas with the tag `broadway.saga`
- The `broadway:event-store:schema:drop` command no longer errors when there is no schema

##### Other changes

- There are now [Saga examples](https://github.com/qandidate-labs/broadway/tree/df7445befdb68c9f8b1795d1c454e0dff06ff7a6/examples/saga)
- The DBALEventStore now also works with mysqli

## v0.6.x

#### BC breaks

- The Scenario for CommandHandling now clears the recorded events after each `then`. So for each then you only need to supply the **newly** recorded events.

## v0.5.x

#### BC breaks

- DomainMessageInterface has been removed, and DomainMessage has been made final.
- Renamed `add` method to `save` for [aggregate root repositories](https://github.com/mbadolato/broadway/commit/9b07dfc4998d70b4c6d25dcacf114a60ea7f1450).

##### Symfony Bundle

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
