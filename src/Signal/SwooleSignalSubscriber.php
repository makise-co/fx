<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Signal;

use Swoole\Coroutine;
use Swoole\Process;

class SwooleSignalSubscriber implements SignalSubscriber
{
    /**
     * @param int[] $signals
     */
    public function wait(array $signals): void
    {
        $chan = new Coroutine\Channel(1);

        $handle = static function (int $signo) use ($signals, $chan) {
            // unsubscribe from signals
            foreach ($signals as $signal) {
                Process::signal($signal, null);
            }

            $chan->push($signo);
        };

        foreach ($signals as $signal) {
            Process::signal($signal, $handle);
        }

        $signo = $chan->pop();

        echo "Received [{$signo}] signal" . PHP_EOL;
    }
}
