<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Domain;

use Broadway\TestCase;

class DomainMessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_getters()
    {
        $id       = 'Hi thur';
        $payload  = new SomeEvent();
        $playhead = 15;
        $metadata = new Metadata(['meta']);
        $type     = 'Broadway.Domain.SomeEvent';

        $domainMessage = DomainMessage::recordNow($id, $playhead, $metadata, $payload);

        $this->assertEquals($id,       $domainMessage->getId());
        $this->assertEquals($payload,  $domainMessage->getPayload());
        $this->assertEquals($playhead, $domainMessage->getPlayhead());
        $this->assertEquals($metadata, $domainMessage->getMetadata());
        $this->assertEquals($metadata, $domainMessage->getMetadata());
        $this->assertEquals($type,     $domainMessage->getType());
    }

    /**
     * @test
     */
    public function it_returns_a_new_instance_with_more_metadata_on_andMetadata()
    {
        $domainMessage = DomainMessage::recordNow('id', 42, new Metadata(), 'payload');

        $this->assertNotSame($domainMessage, $domainMessage->andMetadata(Metadata::kv('foo', 42)));
    }

    /**
     * @test
     */
    public function it_keeps_all_data_the_same_expect_metadata_on_andMetadata()
    {
        $domainMessage = DomainMessage::recordNow('id', 42, new Metadata(), 'payload');

        $newMessage = $domainMessage->andMetadata(Metadata::kv('foo', 42));

        $this->assertSame($domainMessage->getId(), $newMessage->getId());
        $this->assertSame($domainMessage->getPlayhead(), $newMessage->getPlayhead());
        $this->assertSame($domainMessage->getPayload(), $newMessage->getPayload());
        $this->assertSame($domainMessage->getRecordedOn(), $newMessage->getRecordedOn());

        $this->assertNotSame($domainMessage->getMetadata(), $newMessage->getMetadata());
    }

    /**
     * @test
     */
    public function it_merges_the_metadata_instances_on_andMetadata()
    {
        $domainMessage = DomainMessage::recordNow('id', 42, Metadata::kv('bar', 1337), 'payload');

        $newMessage = $domainMessage->andMetadata(Metadata::kv('foo', 42));

        $expected = new Metadata(['bar' => 1337, 'foo' => 42]);
        $this->assertEquals($expected, $newMessage->getMetadata());
    }
}

class SomeEvent
{
}
