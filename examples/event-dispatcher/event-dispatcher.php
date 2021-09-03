<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once __DIR__.'/../bootstrap.php';

$eventDispatcher = new MicroModule\Broadway\EventDispatcher\CallableEventDispatcher();

// You can register any callable
$eventDispatcher->addListener('my_event', function ($arg1, $arg2) {
    echo "Arg1: $arg1\n";
    echo "Arg2: $arg2\n";
});

// Dispatch with an array of arguments
$eventDispatcher->dispatch('my_event', ['one', 'two']);
