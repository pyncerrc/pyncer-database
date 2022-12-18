<?php
namespace Pyncer\Database;

trait TableTrait
{
    private string $table;

    public function getTable(): string
    {
        return $this->table;
    }
    protected function setTable(string $value): static
    {
        $this->table = $value;
        return $this;
    }
}
