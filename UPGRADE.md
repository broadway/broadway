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
