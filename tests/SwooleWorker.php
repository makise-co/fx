<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Tests;

use Swoole\Coroutine;

class SwooleWorker
{
    private bool $shouldStop = false;

    public function start()
    {
        for (; !$this->shouldStop; Coroutine::sleep(0.1)) {
            echo "Working..." . PHP_EOL;
        }
    }

    public function stop()
    {
        $this->shouldStop = true;
    }
}
