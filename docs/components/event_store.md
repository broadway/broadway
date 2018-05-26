Event Store Component
=====================

The event store component provides an interface and several implementations of
an event store.

It currently has an in-memory event store implementation that is useful for using in tests.

The [broadway/event-store-dbal] package provides an event store implementation backed by a
relational database based on [doctrine/dbal].

[broadway/event-store-dbal]: https://github.com/broadway/event-store-dbal
[doctrine/dbal]: https://github.com/doctrine/dbal
