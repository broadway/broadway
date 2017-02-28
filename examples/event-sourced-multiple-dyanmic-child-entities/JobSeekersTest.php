<?php

require_once __DIR__ . '/JobSeekers.php';

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
class JobSeekersCommandHandlerTest extends Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase
{
    private $generator;

    public function setUp()
    {
        parent::setUp();
        $this->generator = new Broadway\UuidGenerator\Rfc4122\Version4Generator();
    }

    protected function createCommandHandler(Broadway\EventStore\EventStore $eventStore, Broadway\EventHandling\EventBus $eventBus)
    {
        $repository = new JobSeekerRepository($eventStore, $eventBus);

        return new JobSeekerCommandHandler($repository);
    }

    /**
     * @test
     */
    public function it_can_start_looking_for_work()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([])
            ->when(new JobSeekerStartLookingForWorkCommand($id))
            ->then([new JobSeekerStartedLookingForWorkEvent($id)]);
    }

    /**
     * @test
     */
    public function it_can_add_a_job()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new JobSeekerStartedLookingForWorkEvent($id)])
            ->when(new AddJobToJobSeekerCommand($id, 'job-000', 'Title Zero', 'Description for zero.'))
            ->then([new JobWasAddedToJobSeekerEvent($id, 'job-000', 'Title Zero', 'Description for zero.')]);
    }

    /**
     * @test
     */
    public function it_can_describe_a_job()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new JobSeekerStartedLookingForWorkEvent($id),
                new JobWasAddedToJobSeekerEvent($id, 'job-000', 'Title Zero', 'Description for zero.'),
            ])
            ->when(new DescribeJobForJobSeekerCommand($id, 'job-000', 'Title Double-Oh-Zero', 'Description for zero.'))
            ->then([new JobWasDescribedForJobSeekerEvent($id, 'job-000', 'Title Double-Oh-Zero', 'Description for zero.')]);
    }

    /**
     * @test
     */
    public function it_applies_the_describe_event_to_the_correct_job()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new JobSeekerStartedLookingForWorkEvent($id),
                new JobWasAddedToJobSeekerEvent($id, 'job-000', 'Title Zero', 'Description for zero.'),
                new JobWasAddedToJobSeekerEvent($id, 'job-001', 'Title One', 'Description for one.'),
                new JobWasAddedToJobSeekerEvent($id, 'job-002', 'Title Two', 'Description for two.'),
            ])

            //
            // Trying to describe jobs with the same name and description should result in no new events since
            // our logic dictates that we ignore describe calls that do not change either the title or the
            // description.
            //

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-000', 'Title Zero', 'Description for zero.'))
            ->then([])

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-001', 'Title One', 'Description for one.'))
            ->then([])

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-002', 'Title Two', 'Description for two.'))
            ->then([])

            //
            // Describing one of the jobs with a different title or description should trigger a job was described
            // event. We have already tested for that and we know it works. What we want to test for now is that the
            // event is only being applied to the instance we expect.
            //

            // Describe job-001 with a new title and description and we expect that the event will be triggered.

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-001', 'Title Double-Oh-ONE!', 'Description for the one.'))
            ->then([new JobWasDescribedForJobSeekerEvent($id, 'job-001', 'Title Double-Oh-ONE!', 'Description for the one.')])

            // Next we describe our other two jobs with the previously specified title and description. If our
            // apply logic is correct, the title and description should still be the same and we should not get
            // a new event.

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-000', 'Title Zero', 'Description for zero.'))
            ->then([])

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-002', 'Title Two', 'Description for two.'))
            ->then([])

            // Lastly, we make one final check to see what happens when we describe job-001 again with the original
            // title and description from what it was first added. Since these values are different, we should see
            // one more describe event to set things back to normal.

            ->when(new DescribeJobForJobSeekerCommand($id, 'job-001', 'Title One', 'Description for one.'))
            ->then([new JobWasDescribedForJobSeekerEvent($id, 'job-001', 'Title One', 'Description for one.')])

        ;
    }

    /**
     * @test
     */
    public function it_can_remove_an_accidentally_added_job()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new JobSeekerStartedLookingForWorkEvent($id),
                new JobWasAddedToJobSeekerEvent($id, 'job-000', 'Title Zero', 'Description for zero.'),
                new JobWasAddedToJobSeekerEvent($id, 'job-001', 'Title One OOPS!', 'Description for one ooops.'),
                new JobWasAddedToJobSeekerEvent($id, 'job-002', 'Title Two', 'Description for two.'),
            ])
            ->when(new RemoveAccidentallyAddedJobFromJobSeekerCommand($id, 'job-001'))
            ->then([new AccidentallyAddedJobWasRemovedFromJobSeekerEvent($id, 'job-001')])

            // To ensure that this command was successful, we try to add the job again. If the job can be added again
            // we know that the removal worked.
            ->when(new AddJobToJobSeekerCommand($id, 'job-001', 'Title Double-Oh-One!', 'Description for the one.'))
            ->then([new JobWasAddedToJobSeekerEvent($id, 'job-001', 'Title Double-Oh-One!', 'Description for the one.')])

        ;
    }

    /**
     * @test
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Job job-000 already assigned to this job seeker.
     */
    public function it_cannot_add_the_same_job_if_job_is_already_assigned()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([
                new JobSeekerStartedLookingForWorkEvent($id),
                new JobWasAddedToJobSeekerEvent($id, 'job-000', 'Title Zero', 'Description for zero.'),
            ])
            ->when(new AddJobToJobSeekerCommand($id, 'job-000', 'Title Double-Oh-Zero!', 'Description for the zero.'))
            ->then([]);
    }

    /**
     * @test
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Job job-000 is not assigned to this job seeker.
     */
    public function it_cannot_describe_a_job_it_knows_nothing_about()
    {
        $id = $this->generator->generate();

        $this->scenario
            ->withAggregateId($id)
            ->given([new JobSeekerStartedLookingForWorkEvent($id)])
            ->when(new DescribeJobForJobSeekerCommand($id, 'job-000', 'Title Double-Oh-Zero', 'Description for zero.'))
            ->then([]);
    }
}
