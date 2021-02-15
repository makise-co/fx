<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Signal;

interface SignalSubscriber
{
    /**
     * @param int[] $signals
     * @return mixed
     */
    public function wait(array $signals);
}
