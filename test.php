<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use MakiseCo\Fx\AmpApp;
use MakiseCo\Fx\ReactApp;
use MakiseCo\Fx\Tests\AmpWorkerProvider;
use MakiseCo\Fx\Tests\ReactWorkerProvider;
use MakiseCo\Fx\Tests\SwooleWorkerProvider;

//$app = new SwooleApp(
//    SwooleWorkerProvider::getFxModule(),
//);

//$app = new ReactApp(
//    ReactWorkerProvider::getFxModule(),
//);

$app = new AmpApp(
    AmpWorkerProvider::getFxModule(),
);

$app->run();
