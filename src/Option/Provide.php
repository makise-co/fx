<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Option;

use Closure;
use MakiseCo\Fx\FxApp;
use MakiseCo\Fx\Provide as ProvideObj;

class Provide implements OptionInterface
{
    private array $functions;

    public function __construct(Closure ...$functions)
    {
        $this->functions = $functions;
    }

    public function apply(FxApp $app): void
    {
        foreach ($this->functions as $function) {
            $app->addProvide(
                new ProvideObj(
                    $function,
                ),
            );
        }
    }
}
