<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\DecimalColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;

abstract class AbstractDecimalColumnQuery extends AbstractColumnQuery implements
    DecimalColumnQueryInterface
{
    private int $precision;
    private int $scale;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        int $precision = 10,
        int $scale = 0,
    ) {
        parent::__construct($connection, $table, $name);

        $this->setPrecision($precision);
        $this->setScale($scale);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        int $precision = 10,
        int $scale = 0,
    ) {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $precision,
            $scale,
        );

        $column->setQuery($query);

        return $column;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
    public function setPrecision(int $value): static
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Precision must be greater than zero.');
        }

        $this->precision = $value;
        return $this;
    }

    public function getScale(): int
    {
        return $this->scale;
    }
    public function setScale(int $value): static
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Scale must be greater than or equal to zero.');
        } elseif ($value > $this->getPrecision()) {
            throw new InvalidArgumentException('Scale must be less than or equal to the precision.');
        }

        $this->scale = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof DecimalColumnQueryInterface) {
            return false;
        }

        if ($this->getPrecision() !== $value->getPrecision()) {
            return false;
        }

        if ($this->getScale() !== $value->getScale()) {
            return false;
        }

        return parent::equals($value);
    }
}
