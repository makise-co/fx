<?php

declare(strict_types=1);

namespace MakiseCo\Fx;

use Closure;

class Invoke
{
    public Closure $target;

    public function __construct(Closure $target)
    {
        $this->target = $target;
    }
}
