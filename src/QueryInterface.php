<?php
namespace Pyncer\Database;

use Pyncer\Database\QueryStringInterface;

interface QueryInterface extends QueryStringInterface
{
    public function execute(?array $params = null): bool|array|object;
}
