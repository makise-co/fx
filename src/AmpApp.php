<?php

declare(strict_types=1);

namespace MakiseCo\Fx;

use Amp\Loop;
use MakiseCo\Fx\Signal\AmpSignalSubscriber;
use Throwable;

class AmpApp extends FxApp
{
    public function run(): void
    {
        try {
            $this->start();
        } catch (Throwable $e) {
            $this->logger->error('Failed to start: ' . (string)$e);

            $this->stopProcess(255);
            return;
        }

        $this->waitForShutdown();
        Loop::run();

        try {
            $this->lifecycle->stop();
        } catch (Throwable $e) {
            $this->logger->error('Failed to stop cleanly: ' . (string)$e);

            $this->stopProcess(255);
            return;
        }
    }

    protected function waitForShutdown(): void
    {
        $signalSubscriber = new AmpSignalSubscriber();
        $promise = $signalSubscriber->wait([SIGINT, SIGTERM, SIGQUIT]);

        $promise->onResolve(function () {
            Loop::stop();
        });
    }
}
