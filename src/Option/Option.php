<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Option;

use MakiseCo\Fx\FxApp;

class Option implements OptionInterface
{
    /**
     * @var OptionInterface[]
     */
    private array $options;

    public function __construct(OptionInterface ...$options)
    {
        $this->options = $options;
    }

    public function apply(FxApp $app): void
    {
        foreach ($this->options as $option) {
            $option->apply($app);
        }
    }
}
