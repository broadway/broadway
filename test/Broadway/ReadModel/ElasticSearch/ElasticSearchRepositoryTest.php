<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\ElasticSearch;

use Broadway\ReadModel\RepositoryTestCase;
use Broadway\Serializer\SerializerInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Elasticsearch\Client;

/**
 * @group functional
 * @requires extension curl
 */
class ElasticSearchRepositoryTest extends RepositoryTestCase
{
    private $client;

    protected function createRepository()
    {
        $this->client = new Client(['hosts' => ['localhost:9200']]);
        $this->client->indices()->create(['index' => 'test_index']);
        $this->client->cluster()->health(['index' => 'test_index', 'wait_for_status' => 'yellow', 'timeout' => '10s']);

        return $this->createElasticSearchRepository(
            $this->client,
            new SimpleInterfaceSerializer(),
            'test_index',
            'Broadway\ReadModel\RepositoryTestReadModel'
        );
    }

    protected function createElasticSearchRepository(Client $client, SerializerInterface $serializer, $index, $class)
    {
        return new ElasticSearchRepository($client, $serializer, $index, $class);
    }

    /**
     * @test
     */
    public function it_creates_an_index_with_non_analyzed_terms()
    {
        $type             = 'class';
        $nonAnalyzedTerm  = 'name';
        $index            = 'test_non_analyzed_index';
        $this->repository = new ElasticSearchRepository(
            $this->client,
            new SimpleInterfaceSerializer(),
            $index,
            $type,
            [$nonAnalyzedTerm]
        );

        $this->repository->createIndex();
        $this->client->cluster()->health(['index' => $index, 'wait_for_status' => 'yellow', 'timeout' => '10s']);
        $mapping = $this->client->indices()->getMapping(['index' => $index]);

        $this->assertArrayHasKey($index, $mapping);
        $this->assertArrayHasKey($type, $mapping[$index]['mappings']);
        $nonAnalyzedTerms = [];

        foreach ($mapping[$index]['mappings'][$type]['properties'] as $key => $value) {
            $nonAnalyzedTerms[] = $key;
        }

        $this->assertEquals([$nonAnalyzedTerm], $nonAnalyzedTerms);
    }

    public function tearDown()
    {
        $this->client->indices()->delete(['index' => 'test_index']);

        if ($this->client->indices()->exists(['index' => 'test_non_analyzed_index'])) {
            $this->client->indices()->delete(['index' => 'test_non_analyzed_index']);
        }
    }
}
