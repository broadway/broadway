Replaying an aggregate
======================

This example demonstrates how Broadway can be used in order to replay a single
aggregate. You have to be cautious when using a replayer, and consider all the
possible consequences of your action. For example, you might not want to resend
all the emails to your customers. In general, replaying EventListeners that
have side effects is discouraged!

You can specify which events need to be loaded from the EventStore using the
\Broadway\EventStore\Management\Criteria object. It allows you to specify all
events corresponding to a specific aggregate root type, all events corresponding
to a single aggregate id (as this example shows), or only events of a specific
type.

The example shows how to use \Broadway\EventStore\Management\EventStoreManagent
to load events and how to pass the events to a
\Broadway\EventStore\EventVisitor. This visitor can be anything you want. In
this example the EventVisitor passes the events to a specific EventListener.
