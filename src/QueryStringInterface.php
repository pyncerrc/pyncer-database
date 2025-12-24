<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Stringable;

interface QueryStringInterface extends Stringable
{
    public function getConnection(): ConnectionInterface;
    public function getQueryString(): string;
}
