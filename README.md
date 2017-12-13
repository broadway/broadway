Broadway
========

Broadway is a project providing infrastructure and testing helpers for creating
CQRS and event sourced applications. Broadway tries hard to not get in your
way. The project contains several loosely coupled components that can be used
together to provide a full CQRS\ES experience.

[![Build Status](https://travis-ci.org/broadway/broadway.svg?branch=master)](https://travis-ci.org/broadway/broadway)

## About

Read the blog post about this repository at:
- http://labs.qandidate.com/blog/2014/08/26/broadway-our-cqrs-es-framework-open-sourced/

## Installation

```
$ composer require broadway/broadway
```

## Documentation
You can find detailed documentation of the Broadway bundle on [broadway.github.io/broadway](https://broadway.github.io/broadway/).

Feel free to join #qandidate on freenode with questions and remarks!

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
