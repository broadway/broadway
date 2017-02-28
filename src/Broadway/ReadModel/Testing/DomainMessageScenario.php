<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\Testing;

use Assert\Assertion;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Broadway\ReadModel\Repository;
use PHPUnit_Framework_TestCase;

/**
 * Helper testing scenario to test projects.
 *
 * The scenario will help with testing projectors. A scenario consists of
 * three steps:
 *
 * 1) given(): Lets the projector handle some domain messages
 * 2) when():  When a specific domain message is handled
 * 3) then():  The repository should contain these read models
 */
class DomainMessageScenario
{
    private $testCase;
    private $projector;
    private $repository;

    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        Repository $repository,
        EventListener $projector
    ) {
        $this->testCase   = $testCase;
        $this->repository = $repository;
        $this->projector  = $projector;
    }

    /**
     * @param DomainMessage[] $domainMessages
     *
     * @return DomainMessageScenario
     */
    public function given(array $domainMessages = [])
    {
        Assertion::allIsInstanceOf($domainMessages, DomainMessage::class);

        foreach ($domainMessages as $given) {
            $this->projector->handle($given);
        }

        return $this;
    }

    /**
     * @param DomainMessage $domainMessage
     *
     * @return DomainMessageScenario
     */
    public function when(DomainMessage $domainMessage)
    {
        $this->projector->handle($domainMessage);

        return $this;
    }

    /**
     * @param array $expectedData
     *
     * @return DomainMessageScenario
     */
    public function then(array $expectedData)
    {
        $this->testCase->assertEquals($expectedData, $this->repository->findAll());

        return $this;
    }
}
