<?php

require_once __DIR__ . '/../bootstrap.php';

/**
 * JobSeeker aggregate root
 */
class JobSeeker extends Broadway\EventSourcing\EventSourcedAggregateRoot
{
    private $jobSeekerId;
    private $jobs = [];

    /**
     * Factory method to create a job seeker.
     */
    public static function startLookingForWork($jobSeekerId)
    {
        $jobSeeker = new JobSeeker();

        // After instantiation of the object we apply the "JobSeekerStartedLookingForWorkEvent".
        $jobSeeker->apply(new JobSeekerStartedLookingForWorkEvent($jobSeekerId));

        return $jobSeeker;
    }

    /**
     * Every aggregate root will expose its id.
     *
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->jobSeekerId;
    }

    public function heldJob($jobId, $title, $description)
    {
        if (array_key_exists($jobId, $this->jobs)) {
            throw new \InvalidArgumentException("Job {$jobId} already assigned to this job seeker.");
        }
        $this->apply(new JobWasAddedToJobSeekerEvent($this->jobSeekerId, $jobId, $title, $description));
    }

    public function removeAccidentallyAddedJob($jobId)
    {
        $this->apply(new AccidentallyAddedJobWasRemovedFromJobSeekerEvent($this->jobSeekerId, $jobId));
    }

    public function describeJob($jobId, $title, $description)
    {
        if (! array_key_exists($jobId, $this->jobs)) {
            throw new \InvalidArgumentException("Job {$jobId} is not assigned to this job seeker.");
        }
        $this->jobs[$jobId]->describe($title, $description);
    }

    public function applyJobSeekerStartedLookingForWorkEvent(JobSeekerStartedLookingForWorkEvent $event)
    {
        $this->jobSeekerId = $event->jobSeekerId;
    }

    public function applyJobWasAddedToJobSeekerEvent(JobWasAddedToJobSeekerEvent $event)
    {
        $this->jobs[$event->jobId] = new Job(
            $event->jobSeekerId,
            $event->jobId,
            $event->title,
            $event->description
        );
    }

    public function applyAccidentallyAddedJobWasRemovedFromJobSeekerEvent(
        AccidentallyAddedJobWasRemovedFromJobSeekerEvent $event
    ) {
        unset($this->jobs[$event->jobId]);
    }

    protected function getChildEntities()
    {
        return $this->jobs;
    }
}

class Job extends Broadway\EventSourcing\SimpleEventSourcedEntity
{
    private $jobSeekerId;
    private $jobId;
    private $title;
    private $description;

    public function __construct($jobSeekerId, $jobId, $title, $description)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
        $this->title       = $title;
        $this->description = $description;
    }

    public function describe($title, $description)
    {
        if ($title === $this->title && $description === $this->description) {
            // If there is no change to the title and description we do not need to
            // generate an event.
            return;
        }

        $this->apply(new JobWasDescribedForJobSeekerEvent(
            $this->jobSeekerId,
            $this->jobId,
            $title,
            $description
        ));
    }

    public function applyJobWasDescribedForJobSeekerEvent(JobWasDescribedForJobSeekerEvent $event)
    {
        if ($event->jobId !== $this->jobId) {
            // Make sure that we only apply events that are intended for
            // *this* job instance and no others.
            return;
        }

        $this->title       = $event->title;
        $this->description = $event->description;
    }
}

class JobSeekerStartLookingForWorkCommand
{
    public $jobSeekerId;

    public function __construct($jobSeekerId)
    {
        $this->jobSeekerId = $jobSeekerId;
    }
}

class JobSeekerStartedLookingForWorkEvent
{
    public $jobSeekerId;

    public function __construct($jobSeekerId)
    {
        $this->jobSeekerId = $jobSeekerId;
    }
}

class AddJobToJobSeekerCommand
{
    public $jobSeekerId;
    public $jobId;
    public $title;
    public $description;

    public function __construct($jobSeekerId, $jobId, $title, $description)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
        $this->title       = $title;
        $this->description = $description;
    }
}

class JobWasAddedToJobSeekerEvent
{
    public $jobSeekerId;
    public $jobId;
    public $title;
    public $description;

    public function __construct($jobSeekerId, $jobId, $title, $description)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
        $this->title       = $title;
        $this->description = $description;
    }
}

class DescribeJobForJobSeekerCommand
{
    public $jobSeekerId;
    public $jobId;
    public $title;
    public $description;

    public function __construct($jobSeekerId, $jobId, $title, $description)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
        $this->title       = $title;
        $this->description = $description;
    }
}

class JobWasDescribedForJobSeekerEvent
{
    public $jobSeekerId;
    public $jobId;
    public $title;
    public $description;

    public function __construct($jobSeekerId, $jobId, $title, $description)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
        $this->title       = $title;
        $this->description = $description;
    }
}

class RemoveAccidentallyAddedJobFromJobSeekerCommand
{
    public $jobSeekerId;
    public $jobId;

    public function __construct($jobSeekerId, $jobId)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
    }
}

class AccidentallyAddedJobWasRemovedFromJobSeekerEvent
{
    public $jobSeekerId;
    public $jobId;

    public function __construct($jobSeekerId, $jobId)
    {
        $this->jobSeekerId = $jobSeekerId;
        $this->jobId       = $jobId;
    }
}

/**
 * A repository that will only store and retrieve JobSeeker aggregate roots.
 */
class JobSeekerRepository extends Broadway\EventSourcing\EventSourcingRepository
{
    public function __construct(Broadway\EventStore\EventStore $eventStore, Broadway\EventHandling\EventBus $eventBus)
    {
        parent::__construct($eventStore, $eventBus, 'JobSeeker', new Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory());
    }
}

/*
 * A command handler will be registered with the command bus and handle the
 * commands that are dispatched.
 */
class JobSeekerCommandHandler extends Broadway\CommandHandling\SimpleCommandHandler
{
    private $repository;

    public function __construct(Broadway\EventSourcing\EventSourcingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * A new job seeker aggregate root is created and added to the repository.
     */
    protected function handleJobSeekerStartLookingForWorkCommand(JobSeekerStartLookingForWorkCommand $command)
    {
        $jobSeeker = JobSeeker::startLookingForWork($command->jobSeekerId);

        $this->repository->save($jobSeeker);
    }

    /**
     * An existing job seeker aggregate root is loaded and heldJob() is
     * called.
     */
    protected function handleAddJobToJobSeekerCommand(AddJobToJobSeekerCommand $command)
    {
        $jobSeeker = $this->repository->load($command->jobSeekerId);

        $jobSeeker->heldJob($command->jobId, $command->title, $command->description);

        $this->repository->save($jobSeeker);
    }

    /**
     * An existing job seeker aggregate root is loaded and describeJob() is
     * called.
     */
    protected function handleDescribeJobForJobSeekerCommand(DescribeJobForJobSeekerCommand $command)
    {
        $jobSeeker = $this->repository->load($command->jobSeekerId);

        $jobSeeker->describeJob($command->jobId, $command->title, $command->description);

        $this->repository->save($jobSeeker);
    }

    /**
     * An existing job seeker aggregate root is loaded and removeAccidentallyAddedJob()
     * is called.
     */
    protected function handleRemoveAccidentallyAddedJobFromJobSeekerCommand(RemoveAccidentallyAddedJobFromJobSeekerCommand $command)
    {
        $jobSeeker = $this->repository->load($command->jobSeekerId);

        $jobSeeker->removeAccidentallyAddedJob($command->jobId);

        $this->repository->save($jobSeeker);
    }
}
