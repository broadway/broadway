<?php

require_once __DIR__ . '/../bootstrap.php';

$eventDispatcher = new Broadway\EventDispatcher\EventDispatcher();

// You can register any callable
$eventDispatcher->addListener('my_event', function($arg1, $arg2) {
    echo "Arg1: $arg1\n";
    echo "Arg2: $arg2\n";
});

// Dispatch with an array of arguments
$eventDispatcher->dispatch('my_event', array('one', 'two'));
