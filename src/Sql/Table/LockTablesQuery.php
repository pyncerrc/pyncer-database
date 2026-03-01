<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\AbstractQuery;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Table\LockTablesInterface;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Exception\RuntimeException;

class LockTablesQuery extends AbstractQuery implements
    LockTablesInterface
{
    use BuildTableTrait;

    private array $tables = [];

    public function write(string|array ...$tables): static
    {
        foreach ($tables as $table) {
            if (is_string($table)) {
                $this->tables[] = [$table, null, 'WRITE'];
                return $this;
            }

            $table = array_values($table);

            $count = count($table);

            switch ($count) {
                case 2:
                    $this->tables[] = [$table[0], $table[1], 'WRITE'];
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        }

        return $this;
    }

    public function read(string|array ...$tables): static
    {
        foreach ($tables as $table) {
            if (is_string($table)) {
                $this->tables[] = [$table, null, 'READ'];
                return $this;
            }

            $table = array_values($table);

            $count = count($table);

            switch ($count) {
                case 2:
                    $this->tables[] = [$table[0], $table[1], 'READ'];
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        }

        return $this;
    }

    public function local(): static
    {
        $count = count($this->tables);

        if ($count === 0) {
            throw new RuntimeException(
                'No tables set.'
            );
        }

        if ($this->tables[$count - 1][2] !== 'READ') {
            throw new RuntimeException(
                'Previously added table does not have the read lock type.'
            );
        }

        $this->tables[$count - 1][2] = 'READ LOCAL';

        return $this;
    }

    public function execute(?array $params = null): bool|array|object
    {
        if (count($this->tables) === 0) {
            throw new RuntimeException(
                'No tables set.'
            );
        }

        return parent::execute($params);
    }

    public function getQueryString(): string
    {
        $query = 'LOCK TABLES .';

        $tables = [];

        foreach ($this->tables as $table) {
            if ($table[1] === null) {
                $tables[] = $this->buildTable($table[0]) . ' ' . $table[2];
            } else {
                $tables[] = $this->buildTable($table[0]) . ' AS ' .
                    $this->buildTable($table[1]) . ' ' . $table[2];
            }
        }

        $query .= implode(', ', $tables);

        return $query;
    }
}
