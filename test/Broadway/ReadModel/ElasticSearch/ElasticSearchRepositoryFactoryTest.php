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

use Broadway\TestCase;

class ElasticSearchRepositoryFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_an_elastic_search_repository()
    {
        $serializer = $this->getMock('Broadway\Serializer\SerializerInterface');
        $client     = $this->getMockBuilder('\Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new ElasticSearchRepository($client, $serializer, 'test', 'Class');
        $factory    = new ElasticSearchRepositoryFactory($client, $serializer);

        $this->assertEquals($repository, $factory->create('test', 'Class'));
    }

    /**
     * @test
     */
    public function it_creates_an_elastic_search_repository_containing_index_metadata()
    {
        $serializer = $this->getMock('Broadway\Serializer\SerializerInterface');
        $client     = $this->getMockBuilder('\Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new ElasticSearchRepository($client, $serializer, 'test', 'Class', ['id']);
        $factory    = new ElasticSearchRepositoryFactory($client, $serializer);

        $this->assertEquals($repository, $factory->create('test', 'Class', ['id']));
    }
}
