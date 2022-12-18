<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\Table\TableQueryInterface;

interface AlterTableQueryInterface extends TableQueryInterface
{
    public function first(?string $columnName = null): static;
    public function after(string $afterColumnName, ?string $columnName = null): static;

    public function rename(string $newColumnName, ?string $columnName = null): static;
}
