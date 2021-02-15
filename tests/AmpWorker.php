<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Tests;

use Amp\Loop;

class AmpWorker
{
    private string $watcherId = '';

    public function start(): void
    {
        $this->watcherId = Loop::repeat(100, function () {
            echo "Working..." . PHP_EOL;
        });
    }

    public function stop(): void
    {
        Loop::cancel($this->watcherId);
    }
}
