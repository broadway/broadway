<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga;

use Assert\Assertion as Assert;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventDispatcher\TraceableEventDispatcher;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaInterface;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaMetadataFactory;
use Broadway\Saga\State\Criteria;
use Broadway\Saga\State\InMemoryRepository;
use Broadway\Saga\State\RepositoryInterface;
use Broadway\Saga\State\StateManager;
use Broadway\TestCase;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;

class MultipleSagaManagerTest extends TestCase
{
    private $manager;
    private $repository;
    private $sagas;
    private $stateManager;
    private $metadataFactory;
    private $eventDispatcher;

    public function setUp()
    {
        $this->repository      = new TraceableSagaStateRepository(new InMemoryRepository());
        $this->sagas           = array('sagaId' => new SagaManagerTestSaga());
        $this->stateManager    = new StateManager($this->repository, new Version4Generator());
        $this->metadataFactory = new StaticallyConfiguredSagaMetadataFactory();
        $this->eventDispatcher = new TraceableEventDispatcher();
        $this->manager         = $this->createManager($this->repository, $this->sagas, $this->stateManager, $this->metadataFactory, $this->eventDispatcher);
    }

    /**
     * @test
     */
    public function it_saves_the_modified_state()
    {
        $s1 = new State(1);
        $s1->set('appId', 42);
        $this->repository->save($s1, 'sagaId');
        $this->repository->trace();

        $this->handleEvent($this->manager, new TestEvent1());

        $saved = $this->repository->getSaved();
        $this->assertCount(1, $saved);
        $this->assertEquals(1, $saved[0]->getId());
        $this->assertEquals('testevent1', $saved[0]->get('event'));
    }

    /**
     * @test
     */
    public function it_removes_the_state_if_the_saga_is_done()
    {
        $s1 = new State(1);
        $s1->set('appId', 42);
        $this->repository->save($s1, 'sagaId');
        $this->repository->trace();

        $this->handleEvent($this->manager, new TestEventDone());

        $removed = $this->repository->getRemoved();
        $this->assertCount(1, $removed);
        $this->assertEquals(1, $removed[0]->getId());
    }

    /**
     * @test
     */
    public function it_creates_and_passes_a_new_saga_state_instance_if_no_criteria_is_configured()
    {
        $this->repository->trace();
        $this->handleEvent($this->manager, new TestEvent2());

        $saved = $this->repository->getSaved();
        $this->assertCount(1, $saved);
        $this->assertEquals('testevent2', $saved[0]->get('event'));
    }

    /**
     * @test
     */
    public function it_does_not_call_the_saga_if_it_is_not_configured_to_handle_an_event()
    {
        foreach ($this->sagas as $saga) {
            $this->assertFalse($saga->isCalled);
        }

        $this->handleEvent($this->manager, new TestEvent3());

        foreach ($this->sagas as $saga) {
            $this->assertFalse($saga->isCalled);
        }
    }

    /**
     * @test
     */
    public function it_does_not_call_the_saga_when_no_state_is_found()
    {
        foreach ($this->sagas as $saga) {
            $this->assertFalse($saga->isCalled);
        }

        $this->handleEvent($this->manager, new TestEvent1());

        foreach ($this->sagas as $saga) {
            $this->assertFalse($saga->isCalled);
        }
    }

    /**
     * @test
     */
    public function it_calls_all_sagas_configured_for_that_event()
    {
        $sagas   = array(new SagaManagerTestSaga(), new SagaManagerTestSaga());
        $manager = $this->createManager($this->repository, $sagas, $this->stateManager, $this->metadataFactory, $this->eventDispatcher);

        foreach ($sagas as $saga) {
            $this->assertFalse($saga->isCalled);
        }

        $this->handleEvent($manager, new TestEvent2());

        foreach ($sagas as $saga) {
            $this->assertTrue($saga->isCalled);
        }
    }

    /**
     * @test
     */
    public function it_calls_all_sagas_configured_for_that_event_even_when_a_state_is_not_found_for_previous_saga()
    {
        $s1 = new State(1);
        $s1->set('appId', 42);
        $this->repository->save($s1, 'saga2');

        $sagas   = array('saga1' => new SagaManagerTestSaga(), 'saga2' => new SagaManagerTestSaga());
        $manager = $this->createManager($this->repository, $sagas, $this->stateManager, $this->metadataFactory, $this->eventDispatcher);

        $this->assertFalse($sagas['saga2']->isCalled);

        $this->handleEvent($manager, new TestEvent1());

        $this->assertTrue($sagas['saga2']->isCalled);
    }

    /**
     * @test
     */
    public function it_gives_every_saga_an_own_stage_even_when_the_criteria_are_the_same()
    {
        $s1 = new State(1);
        $s1->set('appId', 42);
        $this->repository->save($s1, 'saga1');
        $s2 = new State(2);
        $s2->set('appId', 42);
        $this->repository->save($s2, 'saga2');

        $sagas   = array('saga1' => new SagaManagerTestSaga(), 'saga2' => new SagaManagerTestSaga());
        $manager = $this->createManager($this->repository, $sagas, $this->stateManager, $this->metadataFactory, $this->eventDispatcher);

        $this->repository->trace();

        $this->handleEvent($manager, new TestEvent1());

        $saved = $this->repository->getSaved();
        $this->assertCount(2, $saved);
        $this->assertEquals('testevent1', $saved[0]->get('event'));
        $this->assertEquals('testevent1', $saved[1]->get('event'));
    }

    /**
     * @test
     */
    public function it_dispatches_events()
    {
        $stateId = 1;
        $s1      = new State($stateId);
        $s1->set('appId', 42);
        $this->repository->save($s1, 'sagaId');
        $this->handleEvent($this->manager, new TestEvent1());

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        $this->assertCount(2, $dispatchedEvents);

        $this->assertEquals('broadway.saga.pre_handle', $dispatchedEvents[0]['event']);
        $this->assertEquals('sagaId', $dispatchedEvents[0]['arguments'][0]);
        $this->assertEquals($stateId, $dispatchedEvents[0]['arguments'][1]);

        $this->assertEquals('broadway.saga.post_handle', $dispatchedEvents[1]['event']);
        $this->assertEquals('sagaId', $dispatchedEvents[1]['arguments'][0]);
        $this->assertEquals($stateId, $dispatchedEvents[1]['arguments'][1]);
    }

    /**
     * @test
     */
    public function it_dispatches_events_when_no_state_is_found()
    {
        $this->handleEvent($this->manager, new TestEvent2());

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        $this->assertCount(2, $dispatchedEvents);

        $this->assertEquals('broadway.saga.pre_handle', $dispatchedEvents[0]['event']);
        $this->assertEquals('sagaId', $dispatchedEvents[0]['arguments'][0]);
        Assert::uuid($dispatchedEvents[0]['arguments'][1]);

        $this->assertEquals('broadway.saga.post_handle', $dispatchedEvents[1]['event']);
        $this->assertEquals('sagaId', $dispatchedEvents[1]['arguments'][0]);
        Assert::uuid($dispatchedEvents[1]['arguments'][1]);
        $this->assertEquals($dispatchedEvents[0]['arguments'][1], $dispatchedEvents[1]['arguments'][1]);
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_an_event_when_no_saga_is_called()
    {
        $this->handleEvent($this->manager, new TestEvent1());

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        $this->assertCount(0, $dispatchedEvents);
    }

    private function createManager(TraceableSagaStateRepository $repository, array $sagas, StateManager $stateManager, StaticallyConfiguredSagaMetadataFactory $metadataFactory, TraceableEventDispatcher $dispatcher)
    {
        return new MultipleSagaManager($repository, $sagas, $stateManager, $metadataFactory, $dispatcher);
    }

    private function handleEvent($manager, $event)
    {
        $manager->handle(DomainMessage::recordNow(1, 0, new Metadata(array()), $event));
    }
}

class SagaManagerTestSaga implements StaticallyConfiguredSagaInterface
{
    public $isCalled = false;
    public function handle($event, State $state = null)
    {
        $this->isCalled = true;

        if ($event instanceof TestEvent1) {
            $state->set('event', 'testevent1');
        } elseif ($event instanceof TestEvent2) {
            $state->set('event', 'testevent2');
        } elseif ($event instanceof TestEventDone) {
            $state->setDone();
        }

        return $state;
    }

    public static function configuration()
    {
        return array(
            'TestEvent1'    => function () { return new Criteria(array('appId' => 42)); },
            'TestEvent2'    => function () { },
            'TestEventDone' => function () { return new Criteria(array('appId' => 42)); },
        );
    }
}

class TestEvent1
{
}
class TestEvent2
{
}
class TestEvent3
{
}
class TestEventDone
{
}

class TraceableSagaStateRepository implements RepositoryInterface
{
    private $tracing = false;
    private $repository;
    private $saved   = array();
    private $removed = array();

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findOneBy(Criteria $criteria, $sagaId)
    {
        return $this->repository->findOneBy($criteria, $sagaId);
    }

    public function getSaved()
    {
        return $this->saved;
    }

    public function save(State $state, $sagaId)
    {
        $this->repository->save($state, $sagaId);

        if ($this->tracing) {
            if ($state->isDone()) {
                $this->removed[] = $state;
            } else {
                $this->saved[] = $state;
            }
        }
    }

    public function trace()
    {
        $this->tracing = true;
    }

    public function getRemoved()
    {
        return $this->removed;
    }
}
