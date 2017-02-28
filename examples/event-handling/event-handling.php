<?php

require_once __DIR__ . '/../bootstrap.php';

// An event listener implements the handle method
class MyEventListener implements Broadway\EventHandling\EventListener
{
    public function handle(Broadway\Domain\DomainMessage $domainMessage)
    {
        echo "Got a domain message, yay!\n";
    }
}

// Create the event bus and subscribe the created event listener
$eventBus      = new Broadway\EventHandling\SimpleEventBus();
$eventListener = new MyEventListener();
$eventBus->subscribe($eventListener);

// Create a domain event stream to publish
$metadata          = new Broadway\Domain\Metadata(['source' => 'example']);
$domainMessage     = Broadway\Domain\DomainMessage::recordNow(42, 1, $metadata, new stdClass());
$domainEventStream = new Broadway\Domain\DomainEventStream([$domainMessage]);

// Publish the message, and get output from the event handler \o/
$eventBus->publish($domainEventStream);
