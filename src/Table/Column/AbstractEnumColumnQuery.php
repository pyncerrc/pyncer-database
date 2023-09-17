<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\EnumColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;

use function array_values;
use function array_unique;

abstract class AbstractEnumColumnQuery extends AbstractColumnQuery implements
    EnumColumnQueryInterface
{
    private array $values;

    final public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        array $values
    ) {
        parent::__construct($connection, $table, $name);

        $this->setValues($values);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        array $values,
    ): static
    {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            $values,
        );

        $column->setQuery($query);

        return $column;
    }

    public function getValues(): array
    {
        return $this->values;
    }
    public function setValues(array $value): static
    {
        $this->values = array_values(array_unique($value));
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof EnumColumnQueryInterface) {
            return false;
        }

        if ($this->getValues() !== $value->getValues()) {
            return false;
        }

        return parent::equals($value);
    }
}
