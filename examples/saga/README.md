What's a saga?
==============

A [saga](http://cqrs.nu/Faq/sagas) is a "long running" process. It allows you 
to keep track of a (business) transaction that usually spans multiple requests.
A saga can be seen as a [process 
manager](http://www.enterpriseintegrationpatterns.com/ProcessManager.html), 
which coordinates a particular message flow, possibly across bounded contexts.
While listening to events it can decide to dispatch new commands. In order to 
make that decision, a saga can resort to its own state.

Sagas in Broadway
-----------------

In Broadway, a saga is a class that extends `Broadway\Saga\Saga`. It uses the 
same conventions for listening to events (i.e. it has `handle*()` methods for 
each event it's interested in). Instead of a `DomainMessage` instance, each
of the `handle*()` methods receives a `Broadway\Saga\State` object as the
second argument.

A `handle*()` method should always return a `State` object, which will be 
persisted afterwards. The `State` object can be used to store anything that's
needed to retrieve the state of the saga later, or to create new commands. 

Because a saga dispatches new commands, it usually accepts the command bus as 
its first constructor argument. And since it probably needs fresh UUIDs when 
dispatching new commands, it likely needs a UUID generator as well. The rest 
is up to you.

Sample code
-----------

The code in `ReservationSaga.php` is based on an example from the book 
[Exploring CQRS and event sourcing (Microsoft patterns & 
practices)](https://msdn.microsoft.com/en-us/library/jj554200.aspx).

Whenever an order is placed, a reservation is made for the requested number 
of seats. When this reservation was accepted (i.e. the number of requested 
seats didn't exceed the number of available seats), the order itself is marked 
as "booked". When the reservation was rejected, the order itself is rejected.

Please refer to [this 
diagram](https://msdn.microsoft.com/en-us/library/JJ591570.20afccbda270dfd4b9cf0ffac4249b9b%28l=en-us%29.png) 
for a full overview of the different situations that might occur.

Also, take a look at the tests in `ReservationSagaTest.php` to see how you can
prove that your saga implementation is sound.

The PHPUnit tests can be run by changing to this directory and running:

```bash
phpunit .
```
