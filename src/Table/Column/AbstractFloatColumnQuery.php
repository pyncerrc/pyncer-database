<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\FloatColumnQueryInterface;
use Pyncer\Database\Table\Column\FloatSize;
use Pyncer\Database\Table\TableQueryInterface;

abstract class AbstractFloatColumnQuery extends AbstractColumnQuery implements
    FloatColumnQueryInterface
{
    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        FloatSize $size = FloatSize::DOUBLE
    ) {
        parent::__construct($connection, $table, $name);

        $this->setSize($size);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        FloatSize $size = FloatSize::DOUBLE
    ) {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $size,
        );

        $column->setQuery($query);

        return $column;
    }

    public function getSize(): FloatSize
    {
        return $this->size;
    }
    public function setSize(FloatSize $value): static
    {
        $this->size = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof FloatColumnQueryInterface) {
            return false;
        }

        if ($this->getSize() !== $value->getSize()) {
            return false;
        }

        return parent::equals($value);
    }
}
