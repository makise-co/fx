<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Exception;

use Throwable;

class MultiException extends \RuntimeException
{
    /**
     * @var Throwable[]
     */
    private array $exceptions = [];

    public function __construct(Throwable $ex)
    {
        parent::__construct('');

        $this->exceptions[] = $ex;
    }

    public function append(Throwable $ex): void
    {
        $this->exceptions[] = $ex;
    }

    public function merge(MultiException $ex): void
    {
        foreach ($ex->getExceptions() as $exception) {
            $this->exceptions[] = $exception;
        }
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function __toString(): string
    {
        $str = '';

        foreach ($this->exceptions as $exception) {
            $str .= (string)$exception . PHP_EOL;
        }

        return $str;
    }
}
