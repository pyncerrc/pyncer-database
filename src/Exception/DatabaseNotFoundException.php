<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class DatabaseNotFoundException extends RuntimeException implements
    Exception
{
    protected string $database;

    public function __construct(
        string $database,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->database = $database;

        parent::__construct(
            'The specified database, ' . $database . ', was not found.',
            $code,
            $previous
        );
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
