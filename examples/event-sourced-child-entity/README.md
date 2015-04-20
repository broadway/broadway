Event sourced child entity with tests
=====================================

A small example of an implementation of a small domain model that includes an
aggregate root with a child entity. The example consists of two files. The
first file `Parts` contains the implementation of the domain model. The second
file `PartsTest` contains a PHPUnit test suite to test the domain.

The two files contain comments about what is happening.

The PHPUnit tests can be run by changing to this directory and running:

```bash
$ phpunit .
PHPUnit 4.1.0 by Sebastian Bergmann.

.......

Time: 70 ms, Memory: 4.00Mb

OK (7 tests, 9 assertions)
```
