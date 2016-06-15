# Upgrade

## 0.10.x

In 0.10.0 we introduce the streamType. This is a new field in the event store which identifies which events belong together.
For most people this would probably mean it would be equal to the aggregate root.

As we added a new column to the event store, you need to write a database migration. How that migration will look depends on the
size of your application. One way could be by simple add a manual mapping from event -> streamType.
