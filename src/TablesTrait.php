<?php
namespace Pyncer\Database;

trait TablesTrait
{
    /** @var array<string> */
    private array $tables;

    /**
     * Gets an array of table names.
     *
     * @return array<string> An array of table names.
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * Sets an array of table names.
     *
     * @param array<string> $value An array of table names.
     * @return static
     */
    protected function setTables(array $value): static
    {
        $this->tables = $value;
        return $this;
    }
}
