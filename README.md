Broadway
========

Broadway is a project providing infrastructure and testing helpers for creating
CQRS and event sourced applications. Broadway tries hard to not get in your
way. The project contains several loosely coupled components that can be used
together to provide a full CQRS\ES experience.

> Note: while broadway is currently used in production, you should probably
> know what you're doing. ;)

[![Build Status](https://travis-ci.org/broadway/broadway.svg?branch=master)](https://travis-ci.org/broadway/broadway)

Feel free to join #qandidate on freenode with questions and remarks!

## About

Read the blog post about this repository at:
- http://labs.qandidate.com/blog/2014/08/26/broadway-our-cqrs-es-framework-open-sourced/

## Installation

```
$ composer require broadway/broadway
```

## Examples

Examples can be found in the [`examples/`][examples] directory. Most of the
examples focus on showing how one of the components works. There is also a more
[deliberate example][example] using several components and showing how you can
test your event sourced model.

[examples]: examples/
[example]: examples/event-sourced-domain-with-tests/

## Components

Broadway consists of several components. Check out the README's of each
component for more information.

- [Auditing](src/Broadway/Auditing/)
- [CommandHandling](src/Broadway/CommandHandling/)
- [Domain](src/Broadway/Domain/)
- [EventDispatcher](src/Broadway/EventDispatcher/)
- [EventHandling](src/Broadway/EventHandling/)
- [EventSourcing](src/Broadway/EventSourcing/)
- [EventStore](src/Broadway/EventStore/)
- [Processor](src/Broadway/Processor/)
- [ReadModel](src/Broadway/ReadModel/)
- [Repository](src/Broadway/Repository/)
- [Saga](https://github.com/broadway/broadway-saga)
- [Serializer](src/Broadway/Serializer/)

## Integrations

- The broadway project ships with a [bundle] to use with a Symfony application.

- A [Laravel package](https://github.com/nWidart/Laravel-broadway) is also available to allow the use of Broadway inside a Laravel application.

Contributions for integrations with other projects are appreciated!

[bundle]: https://github.com/broadway/broadway-bundle

## Acknowledgements

The broadway project is heavily inspired by other open source project such as
[AggregateSource], [Axon Framework] and [Ncqrs].

[Axon Framework]: http://www.axonframework.org/
[Ncqrs]: https://github.com/ncqrs/ncqrs
[AggregateSource]: https://github.com/yreynhout/AggregateSource

We also like to thank [Benjamin], [Marijn] and [Mathias] for the conversations
we had along the way that helped us shape the broadway project. In particular
Marijn for giving us access to his in-house developed CQRS framework.

[Benjamin]: https://twitter.com/beberlei
[Marijn]: https://twitter.com/huizendveld
[Mathias]: https://twitter.com/mathiasverraes

## License

MIT, see LICENSE.
