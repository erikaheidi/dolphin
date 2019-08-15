#!/usr/bin/env php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Dolphin\Dolphin;
use Dolphin\Core\Config;

$config = new Config(require __DIR__ . '/config.php');
$dolphin = new Dolphin($config);

$dolphin->runCommand($argc, $argv);
