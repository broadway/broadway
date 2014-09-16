<?php

namespace Broadway\ReadModel\DoctrineORM;

use Broadway\ReadModel\ReadModelInterface;
use Broadway\ReadModel\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository implementation using DoctrineORM.
 */
class DoctrineORMRepository implements RepositoryInterface
{
    private $entityManager;
    private $repository;
    private $identifierName;

    /**
     * @param EntityManagerInterface $entityManager  An entity manager instance
     * @param string                 $class          FQCN of the class managed by doctrine
     * @param string                 $identifierName Name of your identifier (eg id, userId)
     */
    public function __construct(EntityManagerInterface $entityManager, $class, $identifierName)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($class);
        $this->identifierName = $identifierName;
    }

    /**
     * {@inheritDoc}
     */
    public function save(ReadModelInterface $data)
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        return $this->repository->findOneBy(array($this->identifierName => $id));
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $fields)
    {
        return $this->repository->findBy($fields);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->repository->findAll();
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
        $this->entityManager->remove($model);
        $this->entityManager->flush();
    }
}
