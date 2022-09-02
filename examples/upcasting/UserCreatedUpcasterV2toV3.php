<?php

declare(strict_types=1);

use Broadway\Upcasting\Upcaster;

require_once __DIR__.'/UserCreatedV2.php';
require_once __DIR__.'/UserCreatedV3.php';

/**
 * @implements Upcaster<UserCreatedV2, UserCreatedV3>
 */
class UserCreatedUpcasterV2toV3 implements Upcaster
{
    /**
     * @param UserCreatedV2 $event
     */
    public function supports($event): bool
    {
        return $event instanceof UserCreatedV2;
    }

    /**
     * @param UserCreatedV2 $event
     */
    public function upcast($event): UserCreatedV3
    {
        return new UserCreatedV3(
            $event->userId,
            $event->name,
            $event->surname,
            -1,
            $event->country
        );
    }
}
