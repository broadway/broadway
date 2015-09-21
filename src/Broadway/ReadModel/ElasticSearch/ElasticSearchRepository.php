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

use Assert\Assertion;
use Broadway\ReadModel\ReadModelInterface;
use Broadway\ReadModel\RepositoryInterface;
use Broadway\Serializer\SerializerInterface;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * Repository implementation using Elasticsearch as storage.
 */
class ElasticSearchRepository implements RepositoryInterface
{
    private $client;
    private $serializer;
    private $index;
    private $class;
    private $notAnalyzedFields;

    /**
     * @param string $index
     * @param string $class
     * @param array  $notAnalyzedFields = array
     */
    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        $index,
        $class,
        array $notAnalyzedFields = []
    ) {
        $this->client            = $client;
        $this->serializer        = $serializer;
        $this->index             = $index;
        $this->class             = $class;
        $this->notAnalyzedFields = $notAnalyzedFields;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ReadModelInterface $data)
    {
        Assertion::isInstanceOf($data, $this->class);

        $serializedReadModel = $this->serializer->serialize($data);

        $params = [
            'index'   => $this->index,
            'type'    => $serializedReadModel['class'],
            'id'      => $data->getId(),
            'body'    => $serializedReadModel['payload'],
            'refresh' => true,
        ];

        $this->client->index($params);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->class,
            'id'    => $id,
        ];

        try {
            $result = $this->client->get($params);
        } catch (Missing404Exception $e) {
            return null;
        }

        return $this->deserializeHit($result);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $fields)
    {
        if (empty($fields)) {
            return [];
        }

        return $this->query($this->buildFindByQuery($fields));
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->query($this->buildFindAllQuery());
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        try {
            $this->client->delete([
                'id'      => $id,
                'index'   => $this->index,
                'type'    => $this->class,
                'refresh' => true,
            ]);
        } catch (Missing404Exception $e) { // It was already deleted or never existed, fine by us!
        }
    }

    private function searchAndDeserializeHits(array $query)
    {
        try {
            $result = $this->client->search($query);
        } catch (Missing404Exception $e) {
            return [];
        }

        if (! array_key_exists('hits', $result)) {
            return [];
        }

        return $this->deserializeHits($result['hits']['hits']);
    }

    /**
     * @param array   $query
     * @param array   $facets
     * @param integer $size
     *
     * @return array
     */
    protected function search(array $query, array $facets = [], $size = 500)
    {
        try {
            return $this->client->search([
                'index' => $this->index,
                'body'  => [
                    'query'  => $query,
                    'facets' => $facets,
                ],
                'size' => $size,
            ]);
        } catch (Missing404Exception $e) {
            return [];
        }
    }

    protected function query(array $query)
    {
        return $this->searchAndDeserializeHits(
            [
                'index' => $this->index,
                'body'  => [
                    'query' => $query,
                ],
                'size'  => 500,
            ]
        );
    }

    private function buildFindByQuery(array $fields)
    {
        return [
            'filtered' => [
                'query' => [
                    'match_all' => [],
                ],
                'filter' => $this->buildFilter($fields)
            ]
        ];
    }

    private function buildFindAllQuery()
    {
        return [
            'match_all' => [],
        ];
    }

    private function deserializeHit(array $hit)
    {
        return $this->serializer->deserialize(
            [
                'class'   => $hit['_type'],
                'payload' => $hit['_source'],
            ]
        );
    }

    private function deserializeHits(array $hits)
    {
        return array_map([$this, 'deserializeHit'], $hits);
    }

    private function buildFilter(array $filter)
    {
        $retval = [];

        foreach ($filter as $field => $value) {
            $retval[] = ['term' => [$field => $value]];
        }

        return ['and' => $retval];
    }

    /**
     * Creates the index for this repository's ReadModel.
     *
     * @return boolean True, if the index was successfully created
     */
    public function createIndex()
    {
        $class = $this->class;

        $indexParams = [
            'index' => $this->index,
        ];

        if (count($this->notAnalyzedFields)) {
            $indexParams['body'] = [
                'mappings' => [
                    $class => [
                        '_source'    => [
                            'enabled' => true
                        ],
                        'properties' => $this->createNotAnalyzedFieldsMapping($this->notAnalyzedFields),
                    ]
                ]
            ];
        }

        $this->client->indices()->create($indexParams);
        $response = $this->client->cluster()->health([
            'index'           => $this->index,
            'wait_for_status' => 'yellow',
            'timeout'         => '5s',
        ]);

        return isset($response['status']) && $response['status'] !== 'red';
    }

    /**
     * Deletes the index for this repository's ReadModel.
     *
     * @return True, if the index was successfully deleted
     */
    public function deleteIndex()
    {
        $indexParams = [
            'index'   => $this->index,
            'timeout' => '5s',
        ];

        $this->client->indices()->delete($indexParams);

        $response = $this->client->cluster()->health([
            'index'           => $this->index,
            'wait_for_status' => 'yellow',
            'timeout'         => '5s',
        ]);

        return isset($response['status']) && $response['status'] !== 'red';
    }

    private function createNotAnalyzedFieldsMapping(array $notAnalyzedFields)
    {
        $fields = [];

        foreach ($notAnalyzedFields as $field) {
            $fields[$field] = [
                'type'  => 'string',
                'index' => 'not_analyzed'
            ];
        }

        return $fields;
    }
}
