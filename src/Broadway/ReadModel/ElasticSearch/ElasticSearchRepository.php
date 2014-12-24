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
    private $objectRepository;

    public function __construct(ElsaticSearchSerializableObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ReadModelInterface $data)
    {
        $this->objectRepository->save($data);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        return $this->objectRepository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $fields)
    {
        if (empty($fields)) {
            return array();
        }

        return $this->objectRepository->query($this->buildFindByQuery($fields));
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->objectRepository->query($this->buildFindAllQuery());
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $this->objectRepository->remove($id);
    }

    private function buildFindByQuery(array $fields)
    {
        return array(
            'filtered' => array(
                'query' => array(
                    'match_all' => array(),
                ),
                'filter' => $this->buildFilter($fields)
            )
        );
    }

    private function buildFindAllQuery()
    {
        return array(
            'match_all' => array(),
        );
    }

    private function buildFilter(array $filter)
    {
        $retval = array();

        foreach ($filter as $field => $value) {
            $retval[] = array('term' => array($field => $value));
        }

        return array('and' => $retval);
    }
}
