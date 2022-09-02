<?php

declare(strict_types=1);

use Broadway\Upcasting\Upcaster;

require_once __DIR__.'/UserCreated.php';
require_once __DIR__.'/UserCreatedV2.php';

/**
 * @implements Upcaster<UserCreated, UserCreatedV2>
 */
class UserCreatedUpcasterV1toV2 implements Upcaster
{
    /**
     * @param UserCreated $event
     */
    public function supports($event): bool
    {
        return $event instanceof UserCreated;
    }

    /**
     * @param UserCreated $event
     */
    public function upcast($event): UserCreatedV2
    {
        return new UserCreatedV2(
            $event->userId,
            $event->name,
            'N/A',
            'N/A'
        );
    }
}
