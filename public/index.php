<?php

declare(strict_types=1);

use Application\Kernel;

require(__DIR__ . '/../vendor/autoload.php');

$kernel = new Kernel();

$kernel->initSettings();
$kernel->initContainer();
$kernel->launch();
