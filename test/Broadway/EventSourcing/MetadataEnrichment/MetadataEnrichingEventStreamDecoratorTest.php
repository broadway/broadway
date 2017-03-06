<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventSourcing\MetadataEnrichment;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\TestCase;

class MetadataEnrichingEventStreamDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_original_eventStream_if_no_enrichers_are_registered()
    {
        $decorator = new MetadataEnrichingEventStreamDecorator();

        $eventStream = $this->createDomainEventStream();

        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);

        $this->assertSame($eventStream, $newEventStream);
    }

    /**
     * @test
     */
    public function it_calls_the_enricher_for_every_event()
    {
        $enricher  = new TracableMetadataEnricher();
        $decorator = new MetadataEnrichingEventStreamDecorator([$enricher]);

        $eventStream = $this->createDomainEventStream();

        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);

        $this->assertEquals(2, $enricher->callCount());
    }

    /**
     * @test
     */
    public function it_returns_a_domain_eventstream_with_messages_with_extra_metadata()
    {
        $enricher  = new TracableMetadataEnricher();
        $decorator = new MetadataEnrichingEventStreamDecorator([$enricher]);

        $eventStream = $this->createDomainEventStream();

        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);

        $messages = iterator_to_array($newEventStream);

        $this->assertCount(2, $messages);

        $expectedMetadata = new Metadata(['bar' => 1337, 'traced' => true]);

        foreach ($messages as $message) {
            $this->assertEquals($expectedMetadata, $message->getMetadata());
        }
    }

    /**
     * @test
     */
    public function it_calls_the_enricher_when_registered_later()
    {
        $constructorEnricher     = new TracableMetadataEnricher();
        $newlyRegisteredEnricher = new TracableMetadataEnricher();
        $decorator               = new MetadataEnrichingEventStreamDecorator([$constructorEnricher]);
        $decorator->registerEnricher($newlyRegisteredEnricher);

        $eventStream    = $this->createDomainEventStream();
        $newEventStream = $decorator->decorateForWrite('id', 'type', $eventStream);

        $this->assertEquals(2, $constructorEnricher->callCount());
        $this->assertEquals(2, $newlyRegisteredEnricher->callCount());
    }

    private function createDomainEventStream()
    {
        $m1 = DomainMessage::recordNow('id', 42, Metadata::kv('bar', 1337), 'payload');
        $m2 = DomainMessage::recordNow('id', 42, Metadata::kv('bar', 1337), 'payload');

        return new DomainEventStream([$m1, $m2]);
    }
}

class TracableMetadataEnricher implements MetadataEnricher
{
    private $calls;

    public function enrich(Metadata $metadata)
    {
        $this->calls[] = $metadata;

        return $metadata->merge(Metadata::kv('traced', true));
    }

    public function callCount()
    {
        return count($this->calls);
    }
}
