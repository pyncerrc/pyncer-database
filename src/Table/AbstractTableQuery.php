<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\AbstractQuery;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\EncodingTrait;
use Pyncer\Database\EngineTrait;
use Pyncer\Database\Exception\ColumnNotFoundException;
use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\CommentTrait;
use Pyncer\Database\Table\ForeignKeyQueryInterface;
use Pyncer\Database\Table\IndexQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\UnexpectedValueException;

abstract class AbstractTableQuery extends AbstractQuery implements
    TableQueryInterface
{
    use CommentTrait;
    use EncodingTrait;
    use EngineTrait;
    use TableTrait;

    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected array $primary = [];

    protected ?ColumnQueryInterface $column = null;
    protected ?IndexQueryInterface $index = null;
    protected ?ForeignKeyQueryInterface $foreignKey = null;
    protected mixed $last = null;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        parent::__construct($connection);

        $this->setTable($table);

        $this->setEngine($this->getConnection()->getEngine());
        $this->setCharacterSet($this->getConnection()->getCharacterSet());
        $this->setCollation($this->getConnection()->getCollation());
    }

    public function serial(string $columnName): static
    {
        return $this->int($columnName, IntSize::BIG)
            ->primary()
            ->autoIncrement();
    }

    public function primary(string ...$columnNames): static
    {
        $columnNames = $this->getColumnNames(...$columnNames);

        $this->primary = [];

        foreach ($columnNames as $columnName) {
            $this->primary[] = $columnName;
        }

        return $this;
    }

    public function autoIncrement(
        string ...$columnNames
    ): static
    {
        $columns = $this->getColumns(...$columnNames);

        foreach ($columns as $column) {
            if (!$column instanceof IntColumnQueryInterface) {
                throw new UnexpectedValueException(
                    'The specified column, ' . $column->getName() . ', does not support auto increment.'
                );
            }

            $column->setAutoIncrement(true);
        }

        return $this;
    }

    public function unsigned(string ...$columnNames): static
    {
        $columns = $this->getColumns(...$columnNames);

        foreach ($columns as $column) {
            if (!$column instanceof IntColumnQueryInterface) {
                throw new UnexpectedValueException(
                    'The specified column, ' . $column->getName() . ', cannot be unsigned.'
                );
            }

            $column->setUnsigned(true);
        }

        return $this;
    }

    public function autoUpdate(
        string ...$columnNames
    ): static
    {
        $columns = $this->getColumns(...$columnNames);

        foreach ($columns as $column) {
            if (!$column instanceof DateTimeColumnQueryInterface) {
                throw new UnexpectedValueException(
                    'The specified column, ' . $column->getName() . ', does not support auto update.'
                );
            }

            $column->setAutoUpdate(true);
        }

        return $this;
    }

    public function null(
        string ...$columnNames
    ): static
    {
        $columns = $this->getColumns(...$columnNames);

        foreach ($columns as $column) {
            $column->setNull(true);
        }

        return $this;
    }

    public function default(
        mixed $value,
        string ...$columnNames
    ): static
    {
        $columns = $this->getColumns(...$columnNames);

        foreach ($columns as $column) {
            $column->setDefault($value);
        }

        return $this;
    }

    public function comment(
        string $value,
        string ...$columnNames
    ): static
    {
        if (!$columnNames && $this->column === null) {
            $this->setComment($value);
            return $this;
        }

        if ($columnNames || $this->last === null) {
            $columns = $this->getColumns(...$columnNames);
        } else {
            // Supports columns, indexes, and foreign keys
            $columns = [$this->last];
        }

        foreach ($columns as $column) {
            $column->setComment($value);
        }

        return $this;
    }

    public function unique(string ...$indexNames): static
    {
        $indexes = $this->getIndexes(...$indexNames);

        foreach ($indexes as $index) {
            $index->setUnique(true);
        }

        return $this;
    }

    public function fulltext(string ...$indexNames): static
    {
        $indexes = $this->getIndexes(...$indexNames);

        foreach ($indexes as $index) {
            $index->setFulltext(true);
        }

        return $this;
    }
    public function references(string $table, string ...$columnNames): static
    {
        $foreignKeys = $this->getForeignKeys();

        foreach ($foreignKeys as $foreignKey) {
            $foreignKey->setReferenceTable($table);
            $foreignKey->setReferenceColumns($columnNames);
        }

        return $this;
    }

    public function deleteAction(
        ReferentialAction $action,
        string ...$foreignKeyNames
    ): static {
        $foreignKeys = $this->getForeignKeys(...$foreignKeyNames);

        foreach ($foreignKeys as $foreignKey) {
            $foreignKey->setDeleteAction($action);
        }

        return $this;
   }

    public function updateAction(
        ReferentialAction $action,
        string ...$foreignKeyNames
    ): static
    {
        $foreignKeys = $this->getForeignKeys(...$foreignKeyNames);

        foreach ($foreignKeys as $foreignKey) {
            $foreignKey->setUpdateAction($action);
        }

        return $this;
    }

    public function engine(string $value): static
    {
        $this->setEngine($value);
        return $this;
    }

    public function characterSet(
        string $value,
        string ...$columnNames
    ): static
    {
        if (!$columnNames && $this->column === null) {
            $this->setCharacterSet($value);
            return $this;
        }

        $columns = $this->getColumns($columnNames);

        foreach ($columns as $column) {
            if (!$column instanceof EncodingInterface) {
                throw new UnexpectedValueException(
                    'The specified column, ' . $column->getName() . ', does not support character sets.'
                );
            }

            $column->setCharacterSet($value);
        }

        return $this;
    }
    public function collation(
        string $value,
        string ...$columnNames
    ): static
    {
        if (!$columnNames && $this->column === null) {
            $this->setCollation($value);
            return $this;
        }

        $columns = $this->getColumns($columnNames);

        foreach ($columns as $column) {
            if (!$column instanceof EncodingInterface) {
                throw new UnexpectedValueException(
                    'The specified column, ' . $column->getName() . ', does not support collations.'
                );
            }

            $column->setCollation($value);
        }

        return $this;
    }

    public function hasColumn(string $columnName): bool
    {
        foreach ($this->columns as $column) {
            if ($column->getNewName() !== null) {
                if ($column->getNewName() === $columnName) {
                    return true;
                }
            } elseif ($column->getName() === $columnName) {
                return true;
            }
        }

        return false;
    }

    public function dropColumn(string $columnName): static
    {
        foreach ($this->columns as $name => $column) {
            if ($column->getNewName() !== null) {
                if ($column->getNewName() === $columnName) {
                    unset($this->columns[$name]);
                    return $this;
                }
            } elseif ($column->getName() === $columnName) {
                unset($this->columns[$name]);
                return $this;
            }
        }

        throw new ColumnNotFoundException($this->getTable(), $columnName);
    }

    public function getColumn(string $columnName): ColumnQueryInterface
    {
        if ($columnName === '') {
            throw new InvalidArgumentException(
                'Column name cannot be an empty string.'
            );
        }

        foreach ($this->columns as $name => $column) {
            if ($column->getNewName() !== null) {
                if ($column->getNewName() === $columnName) {
                    return $column;
                }
            } elseif ($column->getName() === $columnName) {
                return $column;
            }
        }

        throw new ColumnNotFoundException($this->getTable(), $columnName);
    }

    protected function addColumn(ColumnQueryInterface $column): static
    {
        $this->columns[$column->getName()] = $column;
        $this->column = $column;
        $this->last = $column;

        return $this;
    }

    protected function getColumns(string ...$columnNames): array
    {
        if (!$columnNames) {
            if ($this->column === null) {
                throw new UnexpectedValueException(
                    'The table, ' . $this->getTable() . ', does not have any columns.'
                );
            }

            return [$this->column];
        }

        $result = [];

        foreach ($columnNames as $columnName) {
            $result[] = $this->getColumn($columnName);
        }

        return $result;
    }

    protected function getColumnNames(string ...$columnNames): array
    {
        $result = [];

        foreach ($this->getColumns(...$columnNames) as $column) {
            if ($column->getNewName() !== null) {
                $result[] = $column->getNewName();
            } else {
                $result[] = $column->getName();
            }
        }

        return $result;
    }

    public function hasPrimary(): bool
    {
        return (count($this->primary) > 0);
    }

    public function dropPrimary(): static
    {
        $this->primary = [];
    }

    public function getPrimary(): ?array
    {
        if ($this->hasPrimary()) {
            return $this->primary;
        }

        return null;
    }

    public function hasIndex(string $indexName): bool
    {
        return array_key_exists($indexName, $this->indexes);
    }

    public function dropIndex(string $indexName): static
    {
        if ($this->hasIndex($indexName)) {
            unset($this->indexes[$indexName]);
            return $this;
        }

        throw new IndexNotFoundException($this->getTable(), $indexName);
    }

    public function getIndex(string $indexName): IndexQueryInterface
    {
        if ($indexName === '') {
            throw new InvalidArgumentException(
                'Index names cannot be an empty string.'
            );
        }

        if ($this->hasIndex($indexName)) {
            return $this->indexes[$indexName];
        }

        throw new IndexNotFoundException($this->getTable(), $indexName);
    }

    protected function addIndex(IndexQueryInterface $index): static
    {
        $this->indexes[$index->getName()] = $index;
        $this->index = $index;
        $this->last = $index;

        return $this;
    }

    protected function getIndexes(string ...$indexNames): array
    {
        if (!$indexNames) {
            if ($this->index === null) {
                throw new UnexpectedValueException(
                    'The table, ' . $this->getTable() . ', does not have any indexes.'
                );
            }

            return [$this->index];
        }

        $result = [];

        foreach ($indexNames as $indexName) {
            $result[] = $this->getIndex($indexName);
        }

        return $result;
    }

    public function hasForeignKey(string $foreignKeyName): bool
    {
        return array_key_exists($foreignKeyName, $this->foreignKeys);
    }

    public function dropForeignKey(string $foreignKeyName): static
    {
        if ($this->hasForeignKey($foreignKeyName)) {
            unset($this->foreignKeys[$foreignKeyName]);
            return $this;
        }

        throw new ForeignKeyNotFoundException(
            $this->getTable(),
            $foreignKeyName
        );
    }

    public function getForeignKey(
        string $foreignKeyName
    ): ForeignKeyQueryInterface
    {
        if ($foreignKeyName === '') {
            throw new InvalidArgumentException(
                'Foreign key names cannot be an empty string.'
            );
        }

        if ($this->hasForeignKey($foreignKeyName)) {
            return $this->foreignKeys[$foreignKeyName];
        }

        throw new ForeignKeyNotFoundException(
            $this->getTable(),
            $foreignKeyName
        );
    }

    protected function addForeignKey(
        ForeignKeyQueryInterface $foreignKey
    ): static
    {
        // Ensure a matching index exists for this foreign key
        // DBMS will do this automatically, but this will ensure similar naming
        $indexFound = false;

        foreach ($this->indexes as $index) {
            if ($index->getUnique() || $index->getFulltext()) {
                continue;
            }

            if ($index->getColumns() !== $foreignKey->getColumns()) {
                continue;
            }

            $indexFound = true;
        }

        if (!$indexFound) {
            $this->index(null, ...$foreignKey->getColumns());
        }

        $this->foreignKeys[$foreignKey->getName()] = $foreignKey;
        $this->foreignKey = $foreignKey;

        return $this;
    }

    protected function getForeignKeys(string ...$foreignKeyNames): array
    {
        if (!$foreignKeyNames) {
            if ($this->foreignKey === null) {
                throw new UnexpectedValueException(
                    'The table, ' . $this->getTable() . ', does not have any foreign keys.'
                );
            }

            return [$this->foreignKey];
        }

        $result = [];

        foreach ($foreignKeyNames as $foreignKeyName) {
            $result[] = $this->getForeignKey($foreignKeyName);
        }

        return $result;
    }
}
