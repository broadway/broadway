<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2022 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
     * {@inheritDoc}
     */
    public function upcast($event)
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->supports($event)) {
                $event = $upcaster->upcast($event);
            }
        }

        return $event;
    }
}
