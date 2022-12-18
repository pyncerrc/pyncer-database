<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\TableQueryInterface;

abstract class AbstractIntColumnQuery extends AbstractColumnQuery implements
    IntColumnQueryInterface
{
    private IntSize $size;
    private bool $autoIncrement;
    private bool $unsigned;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        IntSize $size = IntSize::LARGE,
        bool $autoIncrement = false,
        bool $unsigned = false,
    ) {
        parent::__construct($connection, $table, $name);

        $this->setSize($size);
        $this->setAutoIncrement($autoIncrement);
        $this->setUnsigned($unsigned);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        IntSize $size = IntSize::LARGE,
    ): static
    {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $size,
        );

        $column->setQuery($query);

        return $column;
    }

    public function getSize(): IntSize
    {
        return $this->size;
    }
    public function setSize(IntSize $value): static
    {
        $this->size = $value;
        return $this;
    }

    public function getAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }
    public function setAutoIncrement(bool $value): static
    {
        $this->autoIncrement = $value;
        return $this;
    }

    public function getUnsigned(): bool
    {
        return $this->unsigned;
    }
    public function setUnsigned(bool $value): static
    {
        $this->unsigned = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof IntColumnQueryInterface) {
            return false;
        }

        if ($this->getSize() !== $value->getSize()) {
            return false;
        }

        if ($this->getAutoIncrement() !== $value->getAutoIncrement()) {
            return false;
        }

        if ($this->getUnsigned() !== $value->getUnsigned()) {
            return false;
        }

        return parent::equals($value);
    }
}
