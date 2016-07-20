<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga;

use Broadway\Domain\Metadata;
use Broadway\TestCase;

class SagaMetadataEnricherTest extends TestCase
{
    private $sagaMetadataEnricher;
    private $metadata;

    public function setUp()
    {
        $this->sagaMetadataEnricher = new SagaMetadataEnricher();
        $this->metadata             = new Metadata(['yolo' => 'tralelo']);
    }

    /**
     * @test
     */
    public function it_stores_the_state()
    {
        $type = 'type';
        $id   = 'id';
        $this->sagaMetadataEnricher->postHandleSaga($type, $id);

        $actual = $this->sagaMetadataEnricher->enrich($this->metadata);

        $expected = $this->metadata->merge(Metadata::kv('saga', ['type' => $type, 'state_id' => $id]));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_uses_the_latest_saga_data_it_received()
    {
        $this->sagaMetadataEnricher->postHandleSaga('type1', 'id1');
        $this->sagaMetadataEnricher->postHandleSaga('type2', 'id2');

        $actual = $this->sagaMetadataEnricher->enrich($this->metadata);

        $expected = $this->metadata->merge(Metadata::kv('saga', ['type' => 'type2', 'state_id' => 'id2']));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_enriches_multiple_instances_of_metadata()
    {
        $this->sagaMetadataEnricher->postHandleSaga('type', 'id');

        $this->sagaMetadataEnricher->enrich($this->metadata);
        $actual = $this->sagaMetadataEnricher->enrich($this->metadata);

        $expected = $this->metadata->merge(Metadata::kv('saga', ['type' => 'type', 'state_id' => 'id']));
        $this->assertEquals($expected, $actual);
    }

    public function enrich(Metadata $metadata)
    {
        if (count($this->sagaData) === 0) {
            return $metadata;
        }

        $newMetadata = new Metadata([['saga' => $this->sagaData]]);
        $metadata    = $metadata->merge($newMetadata);

        return $metadata;
    }
}
