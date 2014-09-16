<?php

namespace Broadway\ReadModel\DoctrineORM;

use Broadway\ReadModel\RepositoryFactoryInterface;
use Broadway\ReadModel\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Creates Doctrine ORM repositories.
 */
class DoctrineORMRepositoryFactory implements RepositoryFactoryInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name  Name of your aggregate identifier field. (example 'id','userId')
     * @param string $class FQCN of the model managed by doctrine
     *
     * @return RepositoryInterface
     */
    public function create($name, $class)
    {
        return new DoctrineORMRepository($this->entityManager, $class, $name);
    }
}
