<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class TableExistsException extends RuntimeException implements
    Exception
{
    protected string $table;

    public function __construct(
        string $table,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->table = $table;

        parent::__construct(
            'The specified table, ' . $table . ', already exists.',
            $code,
            $previous
        );
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
