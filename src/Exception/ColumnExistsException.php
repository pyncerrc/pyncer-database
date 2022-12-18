<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class ColumnExistsException extends RuntimeException implements
    Exception
{
    protected string $table;
    protected string $column;

    public function __construct(
        string $table,
        string $column,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->table = $table;
        $this->column = $column;

        parent::__construct(
            'The specified column, ' . $table . "." . $column . ', already exists.',
            $code,
            $previous
        );
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumn(): string
    {
        return $this->column;
    }
}
