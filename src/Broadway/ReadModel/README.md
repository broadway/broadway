Read Model Component
====================

Add read models to your application.

This component provides storage for your read models, a projector
implementation to create read models from event streams and testing helpers.

### Basic implementation

Note that the repositories are meant for basic read/writes. Use them to create and retrieve read models.
They should not be used to do complex queries. Please use the underlaying storage directly to do more advanced querying.
