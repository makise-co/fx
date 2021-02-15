<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Option;

use Closure;
use MakiseCo\Fx\FxApp;
use MakiseCo\Fx\Invoke as InvokeObj;

class Invoke implements OptionInterface
{
    private array $constructors;

    public function __construct(Closure ...$constructors)
    {
        $this->constructors = $constructors;
    }

    public function apply(FxApp $app): void
    {
        foreach ($this->constructors as $constructor) {
            $app->addInvoke(
                new InvokeObj(
                    $constructor,
                ),
            );
        }
    }
}
