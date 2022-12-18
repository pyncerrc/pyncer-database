<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class DatabaseExistsException extends RuntimeException implements
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
            'The specified database, ' . $database . ', already exists.',
            $code,
            $previous
        );
    }

    public function getDatabase(): string
    {
        return $this->database;
    }
}
