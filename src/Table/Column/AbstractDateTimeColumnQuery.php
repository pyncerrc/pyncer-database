<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Exception\InvalidArgumentException;

abstract class AbstractDateTimeColumnQuery extends AbstractColumnQuery implements
    DateTimeColumnQueryInterface
{
    private int $precision;
    private bool $autoUpdate;

    final public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        int $precision = 0,
        bool $autoUpdate = false,
    ) {
        parent::__construct($connection, $table, $name);

        $this->setPrecision($precision);
        $this->setAutoUpdate($autoUpdate);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        int $precision = 0,
    ): static
    {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $precision,
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
        if ($value < 0 || $value > 6) {
            throw new InvalidArgumentException(
                'Precision must be greater than or equal to zero and less than six.'
            );
        }

        $this->precision = $value;
        return $this;
    }

    public function getAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }
    public function setAutoUpdate(bool $value): static
    {
        $this->autoUpdate = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof DateTimeColumnQueryInterface) {
            return false;
        }

        if ($this->getPrecision() !== $value->getPrecision()) {
            return false;
        }

        return parent::equals($value);
    }
}
