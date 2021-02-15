<?php

declare(strict_types=1);

namespace MakiseCo\Fx\Reflection;

use Closure;
use ReflectionFunction;

use function array_key_exists;
use function debug_backtrace;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

class FxReflection
{
    public static function getCaller(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if (!array_key_exists(2, $trace)) {
            return 'n/a';
        }

        $frame = $trace[2];

        $class = $frame['class'] ?? null;
        $function = $frame['function'] ?? 'n/a';
        $line = $frame['line'] ?? null;
        $file = $frame['file'] ?? null;

        if ($class !== null) {
            return "{$class}::{$function}";
        }

        if ($line === null) {
            return $function ?? '';
        }

        $str = "{$function}:{$line}";
        if ($file !== null) {
            $str .= "in {$file}";
        }

        return $str;
    }

    public static function getFunctionName(Closure $func): string
    {
        $function = new ReflectionFunction($func);
        $class = $function->getClosureScopeClass();

        if ($class !== null) {
            $name = $function->getShortName();
            if ($name === '{closure}') {
                $name .= ":{$function->getStartLine()}";
            }

            return $class->getName() . '::' . $name;
        }

        return $function->getName() . ':' . $function->getStartLine();
    }
}
