<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\State;

use Broadway\Saga\State;
use Broadway\TestCase;
use Broadway\Uuid\Testing\MockUuidGenerator;

class StateManagerTest extends TestCase
{
    private $repository;
    private $manager;

    public function setUp()
    {
        $this->repository = new InMemoryRepository();
        $this->generator = new MockUuidGenerator(42);
        $this->manager = new StateManager($this->repository, $this->generator);
    }

    /**
     * @test
     */
    public function it_returns_a_new_state_object_if_the_criteria_is_null()
    {
        $state = $this->manager->findOneBy(null, 'sagaId');

        $this->assertEquals(new State(42), $state);
    }

    /**
     * @test
     */
    public function it_returns_an_existing_state_instance_matching_the_returned_criteria()
    {
        $state = new State(1337);
        $state->set('appId', 1337);
        $this->repository->save($state, 'sagaId');
        $criteria = new Criteria(array('appId' => 1337));

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        $this->assertEquals($state, $resolvedState);
    }

    /**
     * @test
     */
    public function it_returns_null_when_repository_does_not_find_for_given_criteria()
    {
        $criteria = new Criteria(array('appId' => 1337));

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        $this->assertNull($resolvedState);
    }
}
