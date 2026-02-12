<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\QueryInterface;

interface LockTablesInterface extends QueryInterface
{
    public function write(string|array ...$tables): static;
    public function read(string|array ...$tables): static;
    public function local(): static;
}
