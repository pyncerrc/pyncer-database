<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\Sql\Table\Column\BoolColumnQuery;
use Pyncer\Database\Sql\Table\Column\CharColumnQuery;
use Pyncer\Database\Sql\Table\Column\DateColumnQuery;
use Pyncer\Database\Sql\Table\Column\DateTimeColumnQuery;
use Pyncer\Database\Sql\Table\Column\DecimalColumnQuery;
use Pyncer\Database\Sql\Table\Column\FloatColumnQuery;
use Pyncer\Database\Sql\Table\Column\EnumColumnQuery;
use Pyncer\Database\Sql\Table\Column\IntColumnQuery;
use Pyncer\Database\Sql\Table\Column\TextColumnQuery;
use Pyncer\Database\Sql\Table\Column\TimeColumnQuery;
use Pyncer\Database\Sql\Table\Column\StringColumnQuery;
use Pyncer\Database\Sql\Table\ForeignKeyQuery;
use Pyncer\Database\Sql\Table\IndexQuery;
use Pyncer\Database\Table\Column\BoolColumnQueryInterface;
use Pyncer\Database\Table\Column\CharColumnQueryInterface;
use Pyncer\Database\Table\Column\DateColumnQueryInterface;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Table\Column\DecimalColumnQueryInterface;
use Pyncer\Database\Table\Column\FloatColumnQueryInterface;
use Pyncer\Database\Table\Column\FloatSize;
use Pyncer\Database\Table\Column\EnumColumnQueryInterface;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\Column\TextColumnQueryInterface;
use Pyncer\Database\Table\Column\TextSize;
use Pyncer\Database\Table\Column\TimeColumnQueryInterface;
use Pyncer\Database\Table\Column\StringColumnQueryInterface;

trait TableQueryTrait
{
    public function int(
        string $columnName,
        IntSize $size = IntSize::LARGE,
    ): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof IntColumnQueryInterface) {
                $column->setSize($size);
                return $this->addColumn($column);
            }
        }

        $column = IntColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $size,
        );
        return $this->addColumn($column);
    }

    public function float(
        string $columnName,
        FloatSize $size = FloatSize::DOUBLE,
    ): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof FloatColumnQueryInterface) {
                $column->setSize($size);
                return $this->addColumn($column);
            }
        }

        $column = FloatColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $size,
        );
        return $this->addColumn($column);
    }

    public function decimal(
        string $columnName,
        int $precision = 10,
        int $scale = 0,
    ): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof DecimalColumnQueryInterface) {
                $column->setPrecision($precision);
                $column->setScale($scale);
                return $this->addColumn($column);
            }
        }

        $column = DecimalColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $precision,
            $scale,
        );
        return $this->addColumn($column);
    }

    public function char(string $columnName, int $length): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof CharColumnQueryInterface) {
                $column->setLength($length);
                return $this->addColumn($column);
            }
        }

        $column = CharColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $length,
        );
        return $this->addColumn($column);
    }

    public function string(string $columnName, int $length = 250): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof StringColumnQueryInterface) {
                $column->setLength($length);
                return $this->addColumn($column);
            }
        }

        $column = StringColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $length,
        );
        return $this->addColumn($column);
    }

    public function text(
        string $columnName,
        TextSize $size = TextSize::SMALL,
    ): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof TextColumnQueryInterface) {
                $column->setSite($size);
                return $this->addColumn($column);
            }
        }

        $column = TextColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $size,
        );
        return $this->addColumn($column);
    }

    public function date(string $columnName): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof DateColumnQueryInterface) {
                return $this->addColumn($column);
            }
        }

        $column = DateColumnQuery::fromTableQuery(
            $this,
            $columnName,
        );
        return $this->addColumn($column);
    }

    public function time(string $columnName, int $precision = 0): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof TimeColumnQueryInterface) {
                $column->setPrecision($precision);
                return $this->addColumn($column);
            }
        }

        $column = TimeColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $precision,
        );
        return $this->addColumn($column);
    }

    public function dateTime(string $columnName, int $precision = 0): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof DateTimeColumnQueryInterface) {
                $column->setPrecision($precision);
                return $this->addColumn($column);
            }
        }

        $column = DateTimeColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $precision,
        );

        return $this->addColumn($column);
    }

    public function bool(string $columnName): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof BoolColumnQueryInterface) {
                return $this->addColumn($column);
            }
        }

        $column = BoolColumnQuery::fromTableQuery(
            $this,
            $columnName,
        );

        return $this->addColumn($column);
    }

    public function enum(string $columnName, array $values): static
    {
        if ($this->hasColumn($columnName)) {
            $column = $this->getColumn($columnName);
            if ($column instanceof EnumColumnQueryInterface) {
                $column->setValues($values);
                return $this->addColumn($column);
            }
        }

        $column = EnumColumnQuery::fromTableQuery(
            $this,
            $columnName,
            $values,
        );

        return $this->addColumn($column);
    }

    public function index(
        ?string $indexName = null,
        string ...$columnNames,
    ): static
    {
        $columnNames = $this->getColumnNames(...$columnNames);

        if ($indexName === null) {
            $indexName = implode('__', $columnNames);
        } elseif (substr($indexName, 0, 1) === '#') {
            $indexName = implode('__', $columnNames) .
                '__' . substr($indexName, 1);
        }

        $index = IndexQuery::fromTableQuery(
            $this,
            $indexName,
            ...$columnNames,
        );

        return $this->addIndex($index);
    }

    public function foreignKey(
        ?string $foreignKeyName = null,
        string ...$columnNames,
    ): static
    {
        $columnNames = $this->getColumnNames(...$columnNames);

        if ($foreignKeyName === null) {
            $foreignKeyName = $this->getTable() .
                '__' . implode('__', $columnNames);
        } elseif (substr($foreignKeyName, 0, 1) === '#') {
            $foreignKeyName = $this->getTable() .
                '__' . implode('__', $columnNames) .
                '__' . substr($foreignKeyName, 1);
        }

        $foreignKey = ForeignKeyQuery::fromTableQuery(
            $this,
            $foreignKeyName,
            ...$columnNames,
        );

        $this->addForeignKey($foreignKey);

        return $this;
    }
}
