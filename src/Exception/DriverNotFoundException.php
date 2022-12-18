<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class DriverNotFoundException extends RuntimeException implements
    Exception
{
    protected string $driver;

    public function __construct(
        string $driver,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->driver = $driver;

        parent::__construct(
            'The specified database driver, ' . $driver . ', was not found.',
            $code,
            $previous
        );
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
