<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Tests;

use Closure;
use MakiseCo\Fx\Hook\Hook;
use MakiseCo\Fx\Hook\Lifecycle;
use MakiseCo\Fx\Option as fx;
use Swoole\Coroutine;

class SwooleWorkerProvider
{
    public static function getFxModule(): fx\Option
    {
        return new fx\Option(
            new fx\Provide(Closure::fromCallable('self::provideWorker')),
            new fx\Invoke(Closure::fromCallable('self::registerHooks')),
        );
    }

    private static function provideWorker(): SwooleWorker
    {
        return new SwooleWorker();
    }

    private static function registerHooks(Lifecycle $lifecycle, SwooleWorker $worker): void
    {
        $lifecycle->append(
            new Hook(
                function () use ($worker) {
                    echo "Hook on start" . PHP_EOL;

                    Coroutine::create(Closure::fromCallable([$worker, 'start']));
                },
                function () use ($worker) {
                    echo "Hook on stop" . PHP_EOL;

                    Coroutine::create(Closure::fromCallable([$worker, 'stop']));
                }
            )
        );
    }
}
