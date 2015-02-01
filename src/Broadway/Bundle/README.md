Symfony Bundle
==============

Symfony bundle to integrate Broadway into your symfony application.

> Note: this bundle is far from complete. Please let us know (or send a pull
> request) if you miss any configuration options, etc!

## Usage

Register the bundle in your application kernel:

```
$bundles = array(
    // ..
    new Broadway\Bundle\BroadwayBundle\BroadwayBundle(),
);

```

> Note: in order to use the bundle you need some additional dependencies. See
> the suggest key of the composer.json file.

## Services

Once enabled the bundle will expose several services, such as:

- `broadway.command_handling.command_bus` command bus to inject if you use commands
- `broadway.event_store` alias to the active event store
- `broadway.uuid.generator` active uuid generator

## Tags

The bundle provides several tags to use in your service configuration.

### Domain event listeners

Register listeners (such as projectors) that respond and act on domain events:

```xml
<tag name="broadway.domain.event_listener" />
```

### Event listeners

For example an event listener that collects successfully executed commands:

```xml
<tag name="broadway.event_listener"
    event="broadway.command_handling.command_success"
    method="onCommandHandlingSuccess" />
```

## Metadata enrichers

It is possible to add additional metadata to persisted events. This is useful
for recording extra contextual (auditing) data such as the currently logged in
user, an ip address or some request token.

```xml
<tag name="broadway.metadata_enricher" />
```

## Configuration

There are some basic configuration options available at this point. The
options are mostly targeted on providing different setups based on production
or testing usage.

> Note: at this moment the bundle will always use the default doctrine database
> connection for the event store

```yml
broadway:
    event_store:
        dbal:
            table:            events
            use_binary:       false # If you want to use UUIDs to be stored as BINARY(16), required DBAL >= 2.5.0
    command_handling:
        logger:               false
    saga:
        repository:           ~ # One of "in_memory"; "mongodb"
    read_model:
        repository:           ~ # One of "in_memory"; "elasticsearch"
        elasticsearch:
            hosts:

                # Default:
                - localhost:9200
```
