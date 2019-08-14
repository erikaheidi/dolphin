#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Dolphin\Dolphin;
use Dolphin\Core\Config;

$config = new Config(require __DIR__ . '/config.php');
$dolphin = new Dolphin($config);

$dolphin->runCommand(3, [ 'dolphin', 'inventory', 'json']);
