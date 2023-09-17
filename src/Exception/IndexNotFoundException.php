<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class IndexNotFoundException extends RuntimeException implements
    Exception
{
    protected string $table;
    protected string $index;

    public function __construct(
        string $table,
        string $index,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->table = $table;
        $this->index = $index;

        parent::__construct(
            'The specified index, ' . $table . "." . $index. ', was not found.',
            $code,
            $previous
        );
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
