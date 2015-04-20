<?php

namespace Broadway\ReadModel\ElasticSearch;

use Broadway\ReadModel\ReadModelInterface;
use Broadway\Serializer\SerializerInterface;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class ElsaticSearchSerializableObjectRepository
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
        array $notAnalyzedFields = array()
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
        $serializedReadModel = $this->serializer->serialize($data);

        $params = array(
            'index'   => $this->index,
            'type'    => $serializedReadModel['class'],
            'id'      => $data->getId(),
            'body'    => $serializedReadModel['payload'],
            'refresh' => true,
        );

        $this->client->index($params);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        $params = array(
            'index' => $this->index,
            'type'  => $this->class,
            'id'    => $id,
        );

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
    public function remove($id)
    {
        try {
            $this->client->delete(array(
                'id'      => $id,
                'index'   => $this->index,
                'type'    => $this->class,
                'refresh' => true,
            ));
        } catch (Missing404Exception $e) { // It was already deleted or never existed, fine by us!
        }
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function searchAndDeserializeHits(array $query)
    {
        try {
            $result = $this->client->search($query);
        } catch (Missing404Exception $e) {
            return array();
        }

        if (! array_key_exists('hits', $result)) {
            return array();
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
    public function search(array $query, array $facets = array(), $size = 500)
    {
        try {
            return $this->client->search(array(
                'index' => $this->index,
                'body'  => array(
                    'query'  => $query,
                    'facets' => $facets,
                ),
                'size' => $size,
            ));
        } catch (Missing404Exception $e) {
            return array();
        }
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function query(array $query, $size = 500)
    {
        return $this->searchAndDeserializeHits(
            array(
                'index' => $this->index,
                'body'  => array(
                    'query' => $query,
                ),
                'size'  => $size,
            )
        );
    }

    private function deserializeHit(array $hit)
    {
        return $this->serializer->deserialize(
            array(
                'class'   => $hit['_type'],
                'payload' => $hit['_source'],
            )
        );
    }

    private function deserializeHits(array $hits)
    {
        return array_map(array($this, 'deserializeHit'), $hits);
    }

    /**
     * Creates the index for this repository's ReadModel.
     *
     * @return boolean True, if the index was successfully created
     */
    public function createIndex()
    {
        $class = $this->class;

        $indexParams = array(
            'index' => $this->index,
        );

        if (count($this->notAnalyzedFields)) {
            $indexParams['body'] = array(
                'mappings' => array(
                    $class => array(
                        '_source'    => array(
                            'enabled' => true
                        ),
                        'properties' => $this->createNotAnalyzedFieldsMapping($this->notAnalyzedFields),
                    )
                )
            );
        }

        $this->client->indices()->create($indexParams);
        $response = $this->client->cluster()->health(array(
            'index'           => $this->index,
            'wait_for_status' => 'yellow',
            'timeout'         => '5s',
        ));

        return isset($response['status']) && $response['status'] !== 'red';
    }

    /**
     * Deletes the index for this repository's ReadModel.
     *
     * @return True, if the index was successfully deleted
     */
    public function deleteIndex()
    {
        $indexParams = array(
            'index'   => $this->index,
            'timeout' => '5s',
        );

        $this->client->indices()->delete($indexParams);

        $response = $this->client->cluster()->health(array(
            'index'           => $this->index,
            'wait_for_status' => 'yellow',
            'timeout'         => '5s',
        ));

        return isset($response['status']) && $response['status'] !== 'red';
    }

    /**
     * @param array $notAnalyzedFields
     * @return array
     */
    public function createNotAnalyzedFieldsMapping(array $notAnalyzedFields)
    {
        $fields = array();

        foreach ($notAnalyzedFields as $field) {
            $fields[$field] = array(
                'type'  => 'string',
                'index' => 'not_analyzed'
            );
        }

        return $fields;
    }
} 
