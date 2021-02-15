<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Signal;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;

class AmpSignalSubscriber implements SignalSubscriber
{
    public function wait(array $signals): Promise
    {
        $deferred = new Deferred();
        $subscribers = [];

        $handle = function (string $watcherId, int $signo) use (&$subscribers, $deferred) {
            echo "Received [{$signo}] signal" . PHP_EOL;

            // unsubscribe from signals
            foreach ($subscribers as $subscriber) {
                Loop::cancel($subscriber);
            }

            $deferred->resolve($signo);
        };

        foreach ($signals as $signal) {
            $subscribers[] = Loop::onSignal($signal, $handle);
        }

        return $deferred->promise();
    }
}
