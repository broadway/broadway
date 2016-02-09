<?php

namespace Broadway\ReadModel\ElasticSearch;

use Elasticsearch\Client;

class ElasticSearchClientFactory
{
    /**
     * @param array $config
     *
     * @return Client
     */
    public function create(array $config)
    {
        if (class_exists('\Elasticsearch\ClientBuilder')) {
            return \Elasticsearch\ClientBuilder::fromConfig($config);
        }

        return new Client($config);
    }
}
