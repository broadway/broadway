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
 * Creates the event store schema.
 */
class SchemaEventStoreCreateCommand extends AbstractSchemaEventStoreCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('broadway:event-store:schema:init')
            ->setDescription('Creates the event store schema')
            ->setHelp(
<<<EOT
The <info>%command.name%</info> command creates the schema in the default
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
            $output->writeln('<error>Could not create Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $this->exception->getMessage()));

            return 1;
        }

        $error = false;
        try {
            $schemaManager = $this->connection->getSchemaManager();
            $schema        = $schemaManager->createSchema();
            $eventStore    = $this->getEventStore();

            $table = $eventStore->configureSchema($schema);
            if (null !== $table) {
                $schemaManager->createTable($table);
                $output->writeln('<info>Created Broadway event-store schema</info>');
            } else {
                $output->writeln('<info>Broadway event-store schema already exists</info>');
            }
        } catch (Exception $e) {
            $output->writeln('<error>Could not create Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        return $error ? 1 : 0;
    }
}
