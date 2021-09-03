<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once __DIR__.'/../bootstrap.php';

// An event listener implements the handle method
class MyEventListener implements MicroModule\Broadway\EventHandling\EventListener
{
    public function handle(MicroModule\Broadway\Domain\DomainMessage $domainMessage)
    {
        echo "Got a domain message, yay!\n";
    }
}

// Create the event bus and subscribe the created event listener
$eventBus = new MicroModule\Broadway\EventHandling\SimpleEventBus();
$eventListener = new MyEventListener();
$eventBus->subscribe($eventListener);

// Create a domain event stream to publish
$metadata = new MicroModule\Broadway\Domain\Metadata(['source' => 'example']);
$domainMessage = MicroModule\Broadway\Domain\DomainMessage::recordNow(42, 1, $metadata, new stdClass());
$domainEventStream = new MicroModule\Broadway\Domain\DomainEventStream([$domainMessage]);

// Publish the message, and get output from the event handler \o/
$eventBus->publish($domainEventStream);
