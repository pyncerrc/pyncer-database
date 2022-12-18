<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\EncodingTrait;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\TextColumnQueryInterface;
use Pyncer\Database\Table\Column\TextSize;
use Pyncer\Database\Table\TableQueryInterface;

abstract class AbstractTextColumnQuery extends AbstractColumnQuery implements
    TextColumnQueryInterface
{
    use EncodingTrait;

    private TextSize $size;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        TextSize $size = TextSize::SMALL,
    ) {
        parent::__construct($connection, $table, $name);

        $this->setSize($size);
        $this->setCharacterSet($this->getConnection()->getCharacterSet());
        $this->setCollation($this->getConnection()->getCollation());
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        TextSize $size = TextSize::SMALL,
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

    public function getSize(): TextSize
    {
        return $this->size;
    }
    public function setSize(TextSize $value): static
    {
        $this->size = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof TextColumnQueryInterface) {
            return false;
        }

        if ($this->getSize() !== $value->getSize()) {
            return false;
        }

        if ($this->getCharacterSet() !== $value->getCharacterSet()) {
            return false;
        }

        if ($this->getCollation() !== $value->getCollation()) {
            return false;
        }

        return parent::equals($value);
    }
}
