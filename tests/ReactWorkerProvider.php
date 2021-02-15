<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Tests;

use Closure;
use MakiseCo\Fx\Hook\Hook;
use MakiseCo\Fx\Hook\Lifecycle;
use MakiseCo\Fx\Option as fx;
use React\EventLoop\LoopInterface;

class ReactWorkerProvider
{
    public static function getFxModule(): fx\Option
    {
        return new fx\Option(
            new fx\Provide(Closure::fromCallable('self::provideWorker')),
            new fx\Invoke(Closure::fromCallable('self::registerHooks')),
        );
    }

    private static function provideWorker(LoopInterface $loop): ReactWorker
    {
        return new ReactWorker($loop);
    }

    private static function registerHooks(Lifecycle $lifecycle, ReactWorker $worker): void
    {
        $lifecycle->append(
            new Hook(
                function () use ($worker) {
                    echo "Hook on start" . PHP_EOL;

                    $worker->start();
                },
                function () use ($worker) {
                    echo "Hook on stop" . PHP_EOL;

                    $worker->stop();
                }
            )
        );
    }
}
