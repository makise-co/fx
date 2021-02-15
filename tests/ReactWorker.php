<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Tests;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class ReactWorker
{
    private LoopInterface $loop;
    private TimerInterface $timer;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function start(): void
    {
        $this->timer = $this->loop->addPeriodicTimer(0.1, function () {
            echo "Working..." . PHP_EOL;
        });
    }

    public function stop(): void
    {
        $this->loop->cancelTimer($this->timer);
    }
}
