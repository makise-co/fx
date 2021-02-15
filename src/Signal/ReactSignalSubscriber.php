<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Signal;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use Swoole\Process;

class ReactSignalSubscriber implements SignalSubscriber
{
    private LoopInterface $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Actually it's a non blocking event
     *
     * @param array $signals
     * @return mixed|void
     */
    public function wait(array $signals): Promise
    {
        $deferred = new Deferred();

        $handle = static function (int $signo) use ($deferred) {
            echo "Received [{$signo}] signal" . PHP_EOL;

            // resolve promise on signal arrival
            $deferred->resolve($signo);
        };

        // subscribe for signals
        foreach ($signals as $signal) {
            $this->loop->addSignal($signal, $handle);
        }

        // remove signals subscription on resolve
        $deferred->promise()->always(function () use ($signals, $handle) {
            foreach ($signals as $signal) {
                $this->loop->removeSignal($signal, $handle);
            }
        });

        return $deferred->promise();
    }
}
