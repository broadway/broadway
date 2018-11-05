<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
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
        echo sprintf("[%s] %s - %s\n", $level, $message, json_encode($context));
    }
}
