<?php

declare(strict_types=1);

namespace Broadway\Upcasting;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use PHPUnit\Framework\TestCase;

class SequentialUpcasterChainTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_upcast_domain_messages_sequentially(): void
    {
        $sequentialUpcasterChain = new SequentialUpcasterChain([
            new SomeEventV1toV2Upcaster(),
            new SomeEventV2toV3Upcaster(),
        ]);

        $domainMessage = new DomainMessage(1, 0, new Metadata(), new SomeEvent('matiux'), DateTime::now());

        $upcastedDomainMessage = $sequentialUpcasterChain->upcast($domainMessage);

        self::assertInstanceOf(SomeEventV3::class, $upcastedDomainMessage->getPayload());
        self::assertEquals('N/A', $upcastedDomainMessage->getPayload()->surname);
        self::assertEquals(0, $upcastedDomainMessage->getPayload()->age);
    }
}

class SomeEvent
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class SomeEventV2
{
    public $name;
    public $surname;

    public function __construct(string $name, string $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }
}

class SomeEventV3
{
    public $name;
    public $surname;
    public $age;

    public function __construct(string $name, string $surname, int $age)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->age = $age;
    }
}

class SomeEventV1toV2Upcaster implements Upcaster
{
    public function supports(DomainMessage $domainMessage): bool
    {
        return $domainMessage->getPayload() instanceof SomeEvent;
    }

    public function upcast(DomainMessage $domainMessage): DomainMessage
    {
        $payload = $domainMessage->getPayload();

        $upcastedEvent = new SomeEventV2(
            $payload->name,
            'N/A'
        );

        return new DomainMessage(
            $domainMessage->getId(),
            $domainMessage->getPlayhead(),
            $domainMessage->getMetadata(),
            $upcastedEvent,
            $domainMessage->getRecordedOn()
        );
    }
}

class SomeEventV2toV3Upcaster implements Upcaster
{
    public function supports(DomainMessage $domainMessage): bool
    {
        return $domainMessage->getPayload() instanceof SomeEventV2;
    }

    public function upcast(DomainMessage $domainMessage): DomainMessage
    {
        $payload = $domainMessage->getPayload();

        $upcastedEvent = new SomeEventV3(
            $payload->name,
            $payload->surname,
            0
        );

        return new DomainMessage(
            $domainMessage->getId(),
            $domainMessage->getPlayhead(),
            $domainMessage->getMetadata(),
            $upcastedEvent,
            $domainMessage->getRecordedOn()
        );
    }
}
