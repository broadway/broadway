<?php

if (file_exists($file = __DIR__ . '/../vendor/autoload.php')) {
    $loader = require_once $file;
} else {
    throw new RuntimeException('Install dependencies to the examples.');
}

/**
 * Simple logger to be used in examples.
 */
class StdoutLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        echo sprintf("[%s] %s\n", $level, $message);
    }
}
