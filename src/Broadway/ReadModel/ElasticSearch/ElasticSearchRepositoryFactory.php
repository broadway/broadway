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

use Broadway\ReadModel\RepositoryFactoryInterface;
use Broadway\Serializer\SerializerInterface;
use Elasticsearch\Client;

/**
 * Creates Elasticsearch repositories.
 */
class ElasticSearchRepositoryFactory implements RepositoryFactoryInterface
{
    private $client;
    private $serializer;

    public function __construct(Client $client, SerializerInterface $serializer)
    {
        $this->client     = $client;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function create($name, $class, array $notAnalyzedFields = [])
    {
        return new ElasticSearchRepository($this->client, $this->serializer, $name, $class, $notAnalyzedFields);
    }
}
