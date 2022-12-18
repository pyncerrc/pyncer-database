<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\AbstractColumnQuery;
use Pyncer\Database\Table\Column\DateColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;

abstract class AbstractDateColumnQuery extends AbstractColumnQuery implements
    DateColumnQueryInterface
{
    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
    ): static
    {
        $column = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
        );

        $column->setQuery($query);

        return $column;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof DateColumnQueryInterface) {
            return false;
        }

        return parent::equals($value);
    }
}
