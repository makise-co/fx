<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Option;

use MakiseCo\Fx\FxApp;

interface OptionInterface
{
    public function apply(FxApp $app): void;
}
