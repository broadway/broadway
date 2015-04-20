Event sourced domain with tests
===============================

A small example of an implementation of a small domain model. The example
consists of three files. The first file `Invites` contains the implementation of
the domain model. The second file `InvitesTest` contains a PHPUnit test suite
to only test the Invites model. The third file `InvitationCommandHandlerTest` contains
a PHPunit test suite to test the available commands.

The files contain comments about what is happening.

The PHPUnit tests can be run by changing to this directory and running:

```bash
$ phpunit .
PHPUnit 4.1.0 by Sebastian Bergmann.

..............

Time: 52 ms, Memory: 4.50Mb

OK (14 tests, 19 assertions)
```
