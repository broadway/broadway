<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * Part aggregate root
 */
class Part extends Broadway\EventSourcing\EventSourcedAggregateRoot
{
    private $partId;
    private $manufacturer;

    /**
     * Factory method to create a part.
     */
    public static function manufacture($partId, $manufacturerId, $manufacturerName)
    {
        $part = new Part();

        // After instantiation of the object we apply the "PartWasManufacturedEvent".
        $part->apply(new PartWasManufacturedEvent($partId, $manufacturerId, $manufacturerName));

        return $part;
    }

    /**
     * Every aggregate root will expose its id.
     *
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->partId;
    }

    public function renameManufacturer($manufacturerName)
    {
        $this->manufacturer->rename($manufacturerName);
    }

    public function applyPartWasManufacturedEvent(PartWasManufacturedEvent $event)
    {
        $this->partId = $event->partId;

        // We create the entity in our event handler so that it will be created
        // when the aggregate root is reconstituted from an event stream. Once
        // the child entity is instantiated and returned by getChildEntities()
        // it can emit and apply events itself.
        $this->manufacturer = new Manufacturer(
            $event->partId,
            $event->manufacturerId,
            $event->manufacturerName
        );
    }

    protected function getChildEntities()
    {
        // Since the aggregate root always handles the events first we can rely
        // on $this->manufacturer being set by the time the child entities are
        // requested *provided* PartWasManufacturedEvent is the first event in
        // the event stream.
        return [$this->manufacturer];
    }
}

class Manufacturer extends Broadway\EventSourcing\SimpleEventSourcedEntity
{
    private $partId;
    private $manufacturerId;
    private $manufacturerName;

    public function __construct($partId, $manufacturerId, $manufacturerName)
    {
        $this->partId           = $partId;
        $this->manufacturerId   = $manufacturerId;
        $this->manufacturerName = $manufacturerName;
    }

    public function rename($manufacturerName)
    {
        if ($this->manufacturerName === $manufacturerName) {
            // If the name is not actually different we do not need to do
            // anything here.
            return;
        }

        // This event may also be handled by the aggregate root.
        $this->apply(new PartManufacturerWasRenamedEvent($this->partId, $manufacturerName));
    }

    protected function applyPartManufacturerWasRenamedEvent(PartManufacturerWasRenamedEvent $event)
    {
        $this->manufacturerName = $event->manufacturerName;
    }
}

class ManufacturePartCommand
{
    public $partId;
    public $manufacturerId;
    public $manufacturerName;

    public function __construct($partId, $manufacturerId, $manufacturerName)
    {
        $this->partId           = $partId;
        $this->manufacturerId   = $manufacturerId;
        $this->manufacturerName = $manufacturerName;
    }
}

class PartWasManufacturedEvent
{
    public $partId;
    public $manufacturerId;
    public $manufacturerName;

    public function __construct($partId, $manufacturerId, $manufacturerName)
    {
        $this->partId           = $partId;
        $this->manufacturerId   = $manufacturerId;
        $this->manufacturerName = $manufacturerName;
    }
}

class RenameManufacturerForPartCommand
{
    public $partId;
    public $manufacturerName;

    public function __construct($partId, $manufacturerName)
    {
        $this->partId           = $partId;
        $this->manufacturerName = $manufacturerName;
    }
}

class PartManufacturerWasRenamedEvent
{
    public $partId;
    public $manufacturerName;

    public function __construct($partId, $manufacturerName)
    {
        $this->partId           = $partId;
        $this->manufacturerName = $manufacturerName;
    }
}

/**
 * A repository that will only store and retrieve Part aggregate roots.
 */
class PartRepository extends Broadway\EventSourcing\EventSourcingRepository
{
    public function __construct(Broadway\EventStore\EventStore $eventStore, Broadway\EventHandling\EventBus $eventBus)
    {
        parent::__construct($eventStore, $eventBus, 'Part', new Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory());
    }
}

/*
 * A command handler will be registered with the command bus and handle the
 * commands that are dispatched.
 */
class PartCommandHandler extends Broadway\CommandHandling\SimpleCommandHandler
{
    private $repository;

    public function __construct(Broadway\EventSourcing\EventSourcingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * A new part aggregate root is created and added to the repository.
     */
    protected function handleManufacturePartCommand(ManufacturePartCommand $command)
    {
        $part = Part::manufacture($command->partId, $command->manufacturerId, $command->manufacturerName);

        $this->repository->save($part);
    }

    /**
     * An existing part aggregate root is loaded and renameManufacturerTo() is
     * called.
     */
    protected function handleRenameManufacturerForPartCommand(RenameManufacturerForPartCommand $command)
    {
        $part = $this->repository->load($command->partId);

        $part->renameManufacturer($command->manufacturerName);

        $this->repository->save($part);
    }
}
