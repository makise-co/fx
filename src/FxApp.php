<?php

declare(strict_types=1);

namespace MakiseCo\Fx;

use DI\ContainerBuilder;
use InvalidArgumentException;
use MakiseCo\Fx\Exception\MultiException;
use MakiseCo\Fx\Hook\Lifecycle;
use MakiseCo\Fx\Reflection\FxReflection;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionNamedType;
use Throwable;

use const STDOUT;

abstract class FxApp
{
    protected Lifecycle $lifecycle;
    protected LoggerInterface $logger;
    protected ContainerInterface $container;

    /**
     * @var Invoke[]
     */
    protected array $invokes = [];

    /**
     * @var Provide[]
     */
    protected array $provides = [];

    public function __construct(Option\OptionInterface ...$options)
    {
        $this->logger = $this->createLogger();
        $this->container = $this->createContainer();

        $this->lifecycle = new Lifecycle($this->logger);

        foreach ($options as $option) {
            $option->apply($this);
        }

        foreach ($this->provides as $provide) {
            $this->provide($provide);
        }

        foreach ($this->getAdditionalProviders() as $additionalProvider) {
            $this->provide(new Provide($additionalProvider));
        }

        $this->provide(
            new Provide(
                function (): Lifecycle {
                    return $this->lifecycle;
                },
            )
        );

        $this->executeInvokes();
    }

    protected function createLogger(): LoggerInterface
    {
        $monolog = new Logger('fx');

        $handler = new StreamHandler(
            STDOUT,
            Logger::DEBUG,
            true,
            null,
            false,
        );

        $formatter = new LineFormatter(
            "[%datetime%] %channel%: %message%\n",
            LineFormatter::SIMPLE_DATE,
        );
        $formatter->includeStacktraces(true);

        $handler->setFormatter($formatter);

        $monolog->pushHandler($handler);

        return $monolog;
    }

    protected function createContainer(): ContainerInterface
    {
        return (new ContainerBuilder())->build();
    }

    protected function provide(Provide $provide): void
    {
        $funcName = FxReflection::getFunctionName($provide->target);

        $reflection = new ReflectionFunction($provide->target);
        $type = $reflection->getReturnType();

        if ($type === null || $type->allowsNull()) {
            $message = "Provider function must specify it's own not-nullable return type";

            $this->logger->error("PROVIDE FAILED\t\t{$funcName}: {$message}");

            throw new InvalidArgumentException($message);
        }
        if (!$type instanceof ReflectionNamedType) {
            $message = "Provider function must specify it's own not nullable return type";

            $this->logger->error("PROVIDE FAILED\t\t{$funcName}: {$message}");

            throw new InvalidArgumentException($message);
        }

        $typeName = $type->getName();

        $this->logger->info("PROVIDE\t\t{$typeName} <= {$funcName}");

        $this->container->set($typeName, $provide->target);
    }

    protected function invoke(Invoke $invoke): void
    {
        $funcName = FxReflection::getFunctionName($invoke->target);

        $this->logger->info("INVOKE\t\t{$funcName}");

        $this->container->call($invoke->target);
    }

    /**
     * @throws Throwable
     */
    protected function executeInvokes(): void
    {
        foreach ($this->invokes as $invoke) {
            try {
                $this->invoke($invoke);
            } catch (Throwable $e) {
                $fName = FxReflection::getFunctionName($invoke->target);
                $this->logger->error("INVOKE\t\t{$fName} failed: " . (string)$e);

                throw $e;
            }
        }
    }

    /**
     * Call this method to start application
     *
     * @throws MultiException
     * @throws Throwable
     */
    protected function start(): void
    {
        try {
            $this->lifecycle->start();
        } catch (Throwable $startErr) {
            $this->logger->error('Start failed, rolling back: ' . (string)$startErr);

            try {
                $this->lifecycle->stop();
            } catch (MultiException $stopErr) {
                $errors = new MultiException($startErr);
                $errors->merge($stopErr);

                $this->logger->error("Couldn't rollback cleanly: " . (string)$stopErr);

                throw $errors;
            }

            throw $startErr;
        }

        $this->logger->info('RUNNING');
    }

    public function run(): void
    {
        try {
            $this->start();
        } catch (Throwable $e) {
            $this->logger->error('Failed to start: ' . (string)$e);

            $this->stopProcess(255);
            return;
        }

        $this->waitForShutdown();

        try {
            $this->lifecycle->stop();
        } catch (Throwable $e) {
            $this->logger->error('Failed to stop cleanly: ' . (string)$e);

            $this->stopProcess(255);
            return;
        }
    }

    protected function stopProcess(int $code): void
    {
        exit($code);
    }

    /**
     * This method awaits for any shutdown signal
     */
    abstract protected function waitForShutdown(): void;

    /**
     * Supply additional providers to app
     * @return \Closure[]
     */
    protected function getAdditionalProviders(): array
    {
        return [];
    }

    /**
     * @param Invoke $invoke
     * @internal This method is used to add invoke option
     */
    public function addInvoke(Invoke $invoke): void
    {
        $this->invokes[] = $invoke;
    }

    /**
     * @param Provide $provide
     * @internal This method is used to add provide option
     */
    public function addProvide(Provide $provide): void
    {
        $this->provides[] = $provide;
    }
}
