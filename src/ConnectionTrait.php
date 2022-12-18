<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;

trait ConnectionTrait
{
    private ConnectionInterface $connection;

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
    protected function setConnection(ConnectionInterface $value): static
    {
        $this->connection = $value;
        return $this;
    }
}
