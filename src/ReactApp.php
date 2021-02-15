<?php

declare(strict_types=1);

namespace MakiseCo\Fx;

use MakiseCo\Fx\Signal\ReactSignalSubscriber;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Throwable;

class ReactApp extends FxApp
{
    private LoopInterface $loop;

    public function __construct(Option\OptionInterface ...$options)
    {
        $this->loop = Factory::create();

        parent::__construct(...$options);
    }

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

        // start event loop
        $this->loop->run();

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
        $signalSubscriber = new ReactSignalSubscriber($this->loop);
        $promise = $signalSubscriber->wait([SIGINT, SIGTERM, SIGQUIT]);

        $promise->then(
            function () {
                $this->loop->stop();
            }
        );
    }

    protected function getAdditionalProviders(): array
    {
        return [
            function (): LoopInterface {
                return $this->loop;
            },
        ];
    }
}
