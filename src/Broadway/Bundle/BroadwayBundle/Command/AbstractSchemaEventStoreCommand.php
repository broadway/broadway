<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle\Command;

use Assert\Assertion;
use Broadway\EventStore\DBALEventStore;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractSchemaEventStoreCommand
 */
class AbstractSchemaEventStoreCommand extends DoctrineCommand
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /** @var \Exception */
    protected $exception;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'connection',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Specifies the database connection to use.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $databaseConnectionName = $input->getOption('connection') ?: $this->getContainer()->getParameter('broadway.event_store.dbal.connection');
        Assertion::string($databaseConnectionName, 'Input option "connection" must be of type `string`.');

        try {
            $this->connection = $this->getDoctrineConnection($databaseConnectionName);
        } catch (\Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @return DBALEventStore
     *
     * @throws \RuntimeException
     */
    protected function getEventStore()
    {
        $eventStore = $this->getContainer()->get('broadway.event_store');

        if (!$eventStore instanceof DBALEventStore) {
            throw new \RuntimeException("'broadway.event_store' must be configured as an instance of DBALEventStore");
        }

        return $eventStore;
    }
}
