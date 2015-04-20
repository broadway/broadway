Event sourced multiple dynamic child entities with tests
========================================================

A small example of an implementation of a small domain model that includes an
aggregate root that maintains a collection of child entities. The example
consists of two files. The first file `JobSeekers` contains the implementation
of the domain model. The second file `JobSeekersTest` contains a PHPUnit test
suite to test the domain.

The two files contain comments about what is happening.


The Story
---------

The purpose of this example is to show how dynamically created Child Entities of
an Aggregate Root can be managed. Compare this to a Child Entity whose entire
lifecycle is completely tied to its parent Aggregate Root where it is created
when the Aggregate Root itself is created.

In this contrived domain, a Job Seeker may have held zero or more Jobs. Jobs
can have a title and a description and can be removed from the Job Seeker if
they are added accidentally. Jobs may also be described after they have been
held in case typos are made.

The example shows how to manage the creation of new Child Entities (Jobs), how
to modify Child Entities with events, how to ensure that the correct Child
Entities are updated when Events are applied, and how to remove Child Entities
if needed.

The tests show how the domain can be exercised using commands. It also shows
how to test to ensure that Child Entities are being created, managed, and
moved correctly.


Running Tests
-------------

The PHPUnit tests can be run by changing to this directory and running:

```bash
$ phpunit .
PHPUnit 4.1.0 by Sebastian Bergmann.

.......

Time: 70 ms, Memory: 4.00Mb

OK (7 tests, 9 assertions)
```
