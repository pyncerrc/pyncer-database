<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;
use Throwable;

class QueryException extends RuntimeException implements
    Exception
{
    protected string $query;

    public function __construct(string $query, string $message, int $code = 0, ?Throwable $previous = null)
    {
        $this->query = $query;

        parent::__construct(
            $message . "\nQuery: " . $query,
            $code,
            $previous
        );
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
