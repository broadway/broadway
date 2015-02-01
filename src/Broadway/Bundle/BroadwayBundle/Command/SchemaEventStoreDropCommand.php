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

use Broadway\EventStore\DBALEventStore;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drops the event store schema.
 */
class SchemaEventStoreDropCommand extends DoctrineCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('broadway:event-store:schema:drop')
            ->setDescription('Drops the event store schema')
            ->setHelp(
<<<EOT
The <info>broadway:event-store:schema:drop</info> command drops the schema in the default
connections database:

<info>php app/console broadway:schema:event_store:drop</info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getDoctrineConnection('default');

        $error = false;
        try {
            $schemaManager = $connection->getSchemaManager();
            $eventStore    = $this->getEventStore();

            $table = $eventStore->configureTable();
            $schemaManager->dropTable($table->getName());

            $output->writeln('<info>Dropped Broadway event-store schema</info>');
        } catch (Exception $e) {
            $output->writeln('<error>Could not drop Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        return $error ? 1 : 0;
    }

    private function getEventStore()
    {
        $eventStore = $this->getContainer()->get('broadway.event_store');

        if (! $eventStore instanceof DBALEventStore) {
            throw new RuntimeException("'broadway.event_store' must be configured as an instance of DBALEventStore");
        }

        return $eventStore;
    }
}
