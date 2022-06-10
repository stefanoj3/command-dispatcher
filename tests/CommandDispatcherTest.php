<?php

declare(strict_types=1);

namespace CommandDispatcher;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

use function get_class;

/**
 * @group unit
 */
class CommandDispatcherTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function shouldResolveHandler()
    {
        $command = new class () {
        };

        $handler = new class () {
            public function handle(object $command)
            {
                return $command;
            }
        };

        $handlerName = get_class($handler);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($handlerName)->willReturn(true);
        $container->get($handlerName)->willReturn($handler);

        $sut = new CommandDispatcher(
            $container->reveal(),
            [
                get_class($command) => $handlerName,
            ]
        );

        $result = $sut->handle($command);

        $this->assertSame($result, $command);
    }

    /**
     * @test
     */
    public function shouldThrowWhenHandlerIsNotValid()
    {
        $command = new class () {
        };

        // no handle method
        $handler = new class () {
        };

        $handlerName = get_class($handler);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($handlerName)->willReturn(true);
        $container->get($handlerName)->willReturn($handler);

        $sut = new CommandDispatcher(
            $container->reveal(),
            [
                get_class($command) => $handlerName,
            ]
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not have a `handle` method/');
        $sut->handle($command);
    }

    /**
     * @test
     */
    public function shouldThrowWhenNoHandlerNameIsMapped()
    {
        $command = new class () {
        };

        $container = $this->prophesize(ContainerInterface::class);

        $sut = new CommandDispatcher(
            $container->reveal(),
            []
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/has no corresponding handler/');
        $sut->handle($command);
    }

    /**
     * @test
     */
    public function shouldThrowWhenTheMappingIsInvalid()
    {
        $command = new class () {
        };

        $container = $this->prophesize(ContainerInterface::class);

        $sut = new CommandDispatcher(
            $container->reveal(),
            [
                get_class($command) => 123, // only string is allowed for mapping
            ]
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/should be a string/');
        $sut->handle($command);
    }

    /**
     * @test
     */
    public function shouldThrowWhenTheHandlerIsNotAvailableInTheContainer()
    {
        $command = new class () {
        };

        $container = $this->prophesize(ContainerInterface::class);

        $sut = new CommandDispatcher(
            $container->reveal(),
            [
                get_class($command) => 'non-existing-class',
            ]
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not available in the container/');
        $sut->handle($command);
    }
}
