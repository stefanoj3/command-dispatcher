<?php

declare(strict_types=1);

namespace CommandDispatcher;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function get_class;
use function is_string;
use function method_exists;
use function print_r;
use function sprintf;

class CommandDispatcher implements CommandDispatcherInterface
{
    private ContainerInterface $container;
    private array $commandNameToHandlerNameMap;

    /**
     * @param ContainerInterface $container
     * @param string[] $commandNameToHandlerNameMap
     */
    public function __construct(ContainerInterface $container, array $commandNameToHandlerNameMap)
    {
        $this->container = $container;
        $this->commandNameToHandlerNameMap = $commandNameToHandlerNameMap;
    }

    /**
     * @param object $command
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function handle(object $command)
    {
        $handlerName = $this->resolveHandlerName($command);

        if (!$this->container->has($handlerName)) {
            throw new InvalidArgumentException(
                sprintf('`%s` is not available in the container', $handlerName)
            );
        }

        /** @var object $handler */
        $handler = $this->container->get($handlerName);

        if (!method_exists($handler, 'handle')) {
            throw new InvalidArgumentException(
                sprintf('`%s` does not have a `handle` method', $handlerName)
            );
        }

        return $handler->handle($command);
    }

    /**
     * @param object $command
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveHandlerName(object $command): string
    {
        $commandClassName = get_class($command);

        /** @var mixed $handlerName */
        $handlerName = $this->commandNameToHandlerNameMap[$commandClassName] ?? null;

        if (!$handlerName) {
            throw new InvalidArgumentException(
                sprintf('`%s` has no corresponding handler', $commandClassName)
            );
        }

        if (!is_string($handlerName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'the handler specified for `%s` should be a string, got %s instead',
                    $commandClassName,
                    print_r($handlerName, true),
                )
            );
        }

        return $handlerName;
    }
}
