<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\QueryInterface;

abstract class AbstractQuery implements QueryInterface
{
    use ConnectionTrait;

    public function __construct(ConnectionInterface $connection)
    {
        $this->setConnection($connection);
    }

    public function execute(array $params = null): bool|array|object
    {
        return $this->getConnection()->execute($this->getQueryString(), $params);
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
