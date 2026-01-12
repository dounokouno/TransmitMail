<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/tests',
    ])
    ->append([
        __DIR__ . '/index.php',
        __DIR__ . '/index.test.php',
        __DIR__ . '/lib/TransmitMail.php',
    ]);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
