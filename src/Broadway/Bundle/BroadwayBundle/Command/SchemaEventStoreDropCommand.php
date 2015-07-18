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

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drops the event store schema.
 */
class SchemaEventStoreDropCommand extends AbstractSchemaEventStoreCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('broadway:event-store:schema:drop')
            ->setDescription('Drops the event store schema')
            ->setHelp(
<<<EOT
The <info>%command.name%</info> command drops the schema in the default
connections database:

<info>php app/console %command.name%</info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->connection) {
            $output->writeln('<error>Could not drop Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $this->exception->getMessage()));

            return 1;
        }

        $error = false;
        try {
            $schemaManager = $this->connection->getSchemaManager();
            $eventStore    = $this->getEventStore();

            $table = $eventStore->configureTable();
            if ($schemaManager->tablesExist(array($table->getName()))) {
                $schemaManager->dropTable($table->getName());
                $output->writeln('<info>Dropped Broadway event-store schema</info>');
            } else {
                $output->writeln('<info>Broadway event-store schema does not exist</info>');
            }

        } catch (Exception $e) {
            $output->writeln('<error>Could not drop Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        return $error ? 1 : 0;
    }
}
