<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\EncodingTrait;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\BoolColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Exception\InvalidArgumentException;

abstract class AbstractCharColumnQuery extends AbstractColumnQuery implements
    CharColumnQueryInterface
{
    use EncodingTrait;

    private int $length;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        int $length,
    ) {
        parent::__construct($connection, $table, $name);

        $this->setLength($length);
        $this->setCharacterSet($this->getConnection()->getCharacterSet());
        $this->setCollation($this->getConnection()->getCollation());
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        int $length,
    ): static
    {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $length,
        );

        $column->setQuery($query);

        return $column;
    }

    public function getLength(): int
    {
        return $this->length;
    }
    public function setLength(int $value): static
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Char length must be greater than zero.');
        }

        $this->length = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof CharColumnQueryInterface) {
            return false;
        }

        if ($this->getLength() !== $value->getLength()) {
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
