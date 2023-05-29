<?php

declare(strict_types=1);

use Broadway\Domain\DomainMessage;
use Broadway\Upcasting\Upcaster;

require_once __DIR__.'/UserCreated.php';
require_once __DIR__.'/UserCreatedV2.php';

/**
 * @implements Upcaster<UserCreated, UserCreatedV2>
 */
class UserCreatedUpcasterV1toV2 implements Upcaster
{
    public function supports(DomainMessage $domainMessage): bool
    {
        return $domainMessage->getPayload() instanceof UserCreated;
    }

    public function upcast(DomainMessage $domainMessage): DomainMessage
    {
        $payload = $domainMessage->getPayload();

        $upcastedEvent = new UserCreatedV2(
            $payload->userId,
            $payload->name,
            'N/A',
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
