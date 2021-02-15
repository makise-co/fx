<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Hook;

use MakiseCo\Fx\Exception\MultiException;
use MakiseCo\Fx\Reflection\FxReflection;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

class Lifecycle
{
    /** @var Hook[] */
    private array $hooks = [];
    private int $numStarted = 0;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function append(Hook $hook): void
    {
        $hook->caller = FxReflection::getCaller();
        $this->hooks[] = $hook;
    }

    /**
     * @throws Throwable
     */
    public function start(): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook->onStart !== null) {
                $this->logger->info(sprintf("START\t\t%s", $hook->caller));

                // TODO: Print log hook + caller
                ($hook->onStart)();
            }

            $this->numStarted++;
        }
    }

    /**
     * @throws MultiException
     */
    public function stop(): void
    {
        /** @var MultiException|null $errors */
        $errors = null;

        for (; $this->numStarted > 0; $this->numStarted--) {
            $hook = $this->hooks[$this->numStarted - 1];
            if ($hook->onStop === null) {
                continue;
            }

            $this->logger->info(sprintf("STOP\t\t%s", $hook->caller));

            try {
                ($hook->onStop)();
            } catch (Throwable $e) {
                if ($errors === null) {
                    $errors = new MultiException($e);
                } else {
                    $errors->append($e);
                }
            }
        }

        if ($errors !== null) {
            throw $errors;
        }
    }
}
