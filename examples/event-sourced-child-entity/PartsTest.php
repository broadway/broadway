<?php

require_once __DIR__ . '/Parts.php';

/**
 * We drive the tests of our aggregate root through the command handler.
 *
 * A command handler scenario consists of three steps:
 *
 * - First, the scenario is setup with a history of events that already took place.
 * - Second, a command is dispatched (this is handled by the command handler)
 * - Third, the outcome is asserted. This can either be 1) some events are
 *   recorded, or 2) an exception is thrown.
 */
class PartsCommandHandlerTest extends Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase
{
    private $generator;

    public function setUp()
    {
        parent::setUp();
        $this->generator = new Broadway\UuidGenerator\Rfc4122\Version4Generator();
    }

    protected function createCommandHandler(Broadway\EventStore\EventStore $eventStore, Broadway\EventHandling\EventBus $eventBus)
    {
        $repository = new PartRepository($eventStore, $eventBus);

        return new PartCommandHandler($repository);
    }

    /**
     * @test
     */
    public function it_can_manufacture()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([])
            ->when(new ManufacturePartCommand($id, 'acme', 'Acme, Inc'))
            ->then([new PartWasManufacturedEvent($id, 'acme', 'Acme, Inc')]);
    }

    /**
     * @test
     */
    public function it_can_rename_manufacturer()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new PartWasManufacturedEvent($id, 'acme', 'Acme, Inc')])
            ->when(new RenameManufacturerForPartCommand($id, 'Acme, Inc.'))
            ->then([new PartManufacturerWasRenamedEvent($id, 'Acme, Inc.')]);
    }

    /**
     * @test
     */
    public function it_does_not_rename_manufacturer_to_the_same_name()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new PartWasManufacturedEvent($id, 'acme', 'Acme, Inc'),
                new PartWasManufacturedEvent($id, 'acme', 'Acme, Inc.'),
            ])
            ->when(new RenameManufacturerForPartCommand($id, 'Acme, Inc.'))
            ->then([]);
    }
}
