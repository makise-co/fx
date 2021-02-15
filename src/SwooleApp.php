<?php

declare(strict_types=1);

namespace MakiseCo\Fx;

use Closure;
use MakiseCo\Fx\Signal\SignalSubscriber;
use MakiseCo\Fx\Signal\SwooleSignalSubscriber;
use Swoole\Coroutine;
use Swoole\Event;

use function Swoole\Coroutine\run;

class SwooleApp extends FxApp
{
    protected int $exitCode = 0;

    protected function createSignalSubscriber(): SignalSubscriber
    {
        return new SwooleSignalSubscriber();
    }

    public function run(bool $createEventLoop = true): void
    {
        if ($createEventLoop) {
            $this->runInsideEventLoop();
            return;
        }

        parent::run();
    }

    protected function runInsideEventLoop(): void
    {
        // run code inside event loop
        run(Closure::fromCallable('parent::run'));

        exit($this->exitCode);
    }

    protected function waitForShutdown(): void
    {
        $signalSubscriber = $this->createSignalSubscriber();
        $signalSubscriber->wait([SIGINT, SIGTERM, SIGQUIT]);
    }

    protected function stopProcess(int $code): void
    {
        if (Coroutine::getCid() > 0) {
            $this->exitCode = $code;
            Event::exit();
            return;
        }

        parent::stopProcess($code);
    }
}
