<?php

declare(strict_types=1);

use Broadway\Domain\DomainMessage;
use Broadway\Upcasting\Upcaster;

require_once __DIR__.'/UserCreatedV2.php';
require_once __DIR__.'/UserCreatedV3.php';

/**
 * @implements Upcaster<UserCreatedV2, UserCreatedV3>
 */
class UserCreatedUpcasterV2toV3 implements Upcaster
{
    public function supports(DomainMessage $domainMessage): bool
    {
        return $domainMessage->getPayload() instanceof UserCreatedV2;
    }

    public function upcast(DomainMessage $domainMessage): DomainMessage
    {
        $payload = $domainMessage->getPayload();

        $upcastedEvent = new UserCreatedV3(
            $payload->userId,
            $payload->name,
            $payload->surname,
            -1,
            $payload->country
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
