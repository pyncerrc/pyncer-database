<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Stringable;

interface QueryInterface extends Stringable
{
    public function getConnection(): ConnectionInterface;
    public function getQueryString(): string;
    public function execute(?array $params = null): bool|array|object;
}
