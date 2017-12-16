* Introduction
* Components
    * [Auditing](components/auditing.md)
    * [CommandHandling](components/command_handling.md)
    * [Domain](components/domain.md)
    * [EventDispatcher](components/event_dispatcher.md)
    * [EventHandling](components/event_handling.md)
    * [EventSourcing](components/event_sourcing.md)
    * [EventStore](components/event_store.md)
        * [Doctrine DBAL](https://github.com/broadway/event-store-dbal)
        * [MongoDB](https://github.com/broadway/event-store-mongodb)
    * [Processor](components/processor.md)
    * [ReadModel](components/read_model.md)
        * [Elasticsearch](https://github.com/broadway/read-model-elasticsearch)
        * [MongoDB](https://github.com/broadway/read-model-mongodb)
    * [Repository](components/repository.md)
    * [Saga](https://github.com/broadway/broadway-saga)
    * [Sensitive data handling](https://github.com/broadway/broadway-sensitive-data)
    * [Serializer](components/serializer.md)
    * [Snapshotting](https://github.com/broadway/snapshotting)
* [Integrations](integrations.md)
* [Examples](examples.md)

# Introduction

Broadway is a project providing infrastructure and testing helpers for creating
CQRS and event sourced applications. Broadway tries hard to not get in your
way. The project contains several loosely coupled components that can be used
together to provide a full CQRS\ES experience.

Read the blog post about this repository at: [Bringing CQRS and Event Sourcing to PHP. Open sourcing Broadway!](http://labs.qandidate.com/blog/2014/08/26/broadway-our-cqrs-es-framework-open-sourced/)
