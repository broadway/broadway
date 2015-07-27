<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Upcasting;

final class SequentialUpcasterChain implements UpcasterChain
{
    /**
     * @var Upcaster[]
     */
    private $upcasters;

    /**
     * @param Upcaster[] $upcasters
     */
    public function __construct(array $upcasters)
    {
        $this->upcasters = $upcasters;
    }

    /**
     * @param array $serializedEvent
     *
     * @return array the upcasted objects
     */
    public function upcast(array $serializedEvent)
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->supports($serializedEvent)) {
                $serializedEvent = $upcaster->upcast($serializedEvent);
            }
        }

        return $serializedEvent;
    }
}
