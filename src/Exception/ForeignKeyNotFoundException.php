<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class ForeignKeyNotFoundException extends RuntimeException implements
    Exception
{
    protected string $table;
    protected string $foreignKey;

    public function __construct(
        string $table,
        string $foreignKey,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->table = $table;
        $this->foreignKey = $foreignKey;

        parent::__construct(
            'The specified foreign key, ' . $table . "." . $foreignKey . ', was not found.',
            $code,
            $previous
        );
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }
}
