# Upgrade to 2.0

## Concrete classes are made final

As it is no longer possible to extend these classes it is necessary to use composition for code reuse.

## PHP 7

Many interfaces have updated method signatures because of added (return) type hints. When implementing
these interfaces the method signatures must adhere to the parent's signatures.

## PHPUnit 6
PHPUnit is required when you use Broadway's test helpers like Scenarios and base test cases in
the `src/*/Testing/*` directories. In this case you will need to update your projects to PHPUnit 6.

## Test helpers
The RepositoryTestCase, EventStoreTest, EventStoreManagementTest are moved from `test` to `src` and into
the `Testing` namespace. It's now easier to use them without autoloading magic but you need to reimport
the classes with the updated namespace in your project.

# Upgrade to 1.0

## Symfony bundle, DBAL event store, Elasticsearch read models and saga are moved to separate repositories.

To retain these functionalities you need to install the following packages:

```
composer require broadway/broadway-bundle
composer require broadway/event-store-dbal
composer require broadway/read-model-elasticsearch
composer require broadway/broadway-saga
```

You can also check the [Broadway demo project](https://github.com/broadway/broadway-demo). 

## New bundle configuration

The bundle allows you to configure your own event store or read model 
implementation using service ids. 

It no longer configures the DBAL event store and Elasticsearch read models 
by default. For more information check the bundle's [README.md](https://github.com/broadway/broadway-bundle/blob/master/README.md).  

## Renamed interfaces and simple implementations

Most interfaces were changed to remove the `Interface` suffix. This meant
also some simple implementations provided by Broadway were changed.

This is the complete list of changes:

### Renamed interfaces

* Broadway/Auditing/CommandSerializerInterface -> CommandSerializer
* Broadway/CommandHandling/CommandBusInterface -> CommandBus
* Broadway/CommandHandling/CommandHandlerInterface -> CommandHandler
* Broadway/EventDispatcher/EventDispatcherInterface -> EventDispatcher
* Broadway/EventHandling/EventBusInterface -> EventBus
* Broadway/EventHandling/EventListenerInterface -> EventListener
* Broadway/EventSourcing/AggregateFactory/AggregateFactoryInterface -> AggregateFactory
* Broadway/EventSourcing/EventSourcedEntityInterface -> EventSourcedEntity
* Broadway/EventSourcing/EventStreamDecoratorInterface -> EventStreamDecorator
* Broadway/EventSourcing/MetadataEnrichment/MetadataEnricherInterface -> MetadataEnricher
* Broadway/EventStore/EventStoreInterface -> EventStore
* Broadway/EventStore/EventVisitorInterface -> EventVisitor
* Broadway/EventStore/Management/EventStoreManagementInterface -> EventStoreManagement
* Broadway/ReadModel/ReadModelInterface -> Identifiable
* Broadway/ReadModel/RepositoryFactoryInterface -> RepositoryFactory
* Broadway/ReadModel/RepositoryInterface -> Repository
* Broadway/ReadModel/SerializableReadModelInterface -> SerializableReadModel
* Broadway/ReadModel/TransferableInterface -> Transferable
* Broadway/Repository/RepositoryInterface -> Repository
* Broadway/Serializer/SerializableInterface -> Serializable
* Broadway/Serializer/SerializerInterface -> Serializer

### Renamed implementations

* Broadway/Auditing/CommandSerializer -> NullByteCommandSerializer
* Broadway/CommandHandling/CommandHandler -> SimpleCommandHandler
* Broadway/EventDispatcher/EventDispatcher -> CallableEventDispatcher
* Broadway/EventSourcing/EventSourcedEntity -> SimpleEventSourcedEntity
* Broadway/ReadModel/ReadModelTestCase -> SerializableReadModelTestCase

### Dropped interfaces
* Broadway/Domain/DomainEventStreamInterface
* Broadway/ReadModel/ProjectorInterface
