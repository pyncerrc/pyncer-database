<?php
namespace Pyncer\Database;

trait TablesTrait
{
    private array $tables;

    public function getTables(): array
    {
        return $this->tables;
    }
    protected function setTables(array $value): static
    {
        $this->tables = $value;
        return $this;
    }
}
