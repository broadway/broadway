<?php

namespace Broadway\ReadModel\Doctrine;

use Broadway\ReadModel\RepositoryFactoryInterface;
use Broadway\ReadModel\RepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Creates Doctrine repositories.
 */
class DoctrineRepositoryFactory implements RepositoryFactoryInterface
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $name  Name of your aggregate identifier field. (example 'id','userId')
     * @param string $class FQCN of the model managed by doctrine
     *
     * @return RepositoryInterface
     */
    public function create($name, $class)
    {
        return new DoctrineRepository($this->objectManager, $class, $name);
    }
}
