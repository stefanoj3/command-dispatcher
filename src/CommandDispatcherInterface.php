<?php

declare(strict_types=1);

namespace CommandDispatcher;

use InvalidArgumentException;

interface CommandDispatcherInterface
{
    /**
     * @param object $command
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function handle(object $command);
}
