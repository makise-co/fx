<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Hook;

use Closure;

class Hook
{
    public ?Closure $onStart;
    public ?Closure $onStop;
    public string $caller = '';

    public function __construct(?Closure $onStart = null, ?Closure $onStop = null)
    {
        $this->onStart = $onStart;
        $this->onStop = $onStop;
    }
}
