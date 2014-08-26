<?php

require_once __DIR__ . '/../bootstrap.php';

function generateAndOutput5(Broadway\Uuid\UuidGenerator $generator) {
    for ($i = 0; $i < 8; $i++) {
        echo sprintf("[%d] %s\n", $i, $generator->generate());
    }
}

echo "A random generated uuid:\n";
$randomGenerator = new Broadway\Uuid\Rfc4122\Version4Generator();
generateAndOutput5($randomGenerator);

echo "\n";

echo "A generator that will always return the same uuid (for testing):\n";
$mockUuidGenerator = new Broadway\Uuid\Testing\MockUuidGenerator(42);
generateAndOutput5($mockUuidGenerator);

echo "\n";

echo "A generator that will always return the same sequence of uuids and throw an exception if depleted (for testing):\n";
$mockUuidSequenceGenerator = new Broadway\Uuid\Testing\MockUuidSequenceGenerator(array(1, 1, 2, 3, 5, 8, 13, 21));
generateAndOutput5($mockUuidSequenceGenerator);
