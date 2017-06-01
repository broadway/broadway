<?php

namespace Broadway\ReadModel\Doctrine;

use Broadway\ReadModel\ReadModelInterface;
use Broadway\ReadModel\RepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Repository implementation using Doctrine.
 */
class DoctrineRepository implements RepositoryInterface
{
    private $objectManager;
    private $class;
    private $identifierName;

    /**
     * @param ObjectManager          $objectManager  An object manager instance
     * @param string                 $class          FQCN of the class managed by doctrine
     * @param string                 $identifierName Name of your identifier (eg id, userId)
     */
    public function __construct(ObjectManager $objectManager, $class, $identifierName)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
        $this->identifierName = $identifierName;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ReadModelInterface $data)
    {
        $this->objectManager->persist($data);
        $this->objectManager->flush($data);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        return $this->getRepository()->findOneBy(array($this->identifierName => $id));
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $fields)
    {
        return $this->getRepository()->findBy($fields);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $model = $this->find($id);
        if ($model === null) {
            return;
        }
        $this->objectManager->remove($model);
        $this->objectManager->flush($model);
    }

    /**
     * @return ObjectRepository
     */
    private function getRepository()
    {
        return $this->objectManager->getRepository($this->class);
    }
}
