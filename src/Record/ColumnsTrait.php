<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Exception\InvalidArgumentException;

trait ColumnsTrait
{
    protected array $columns = [];

    public function columns(string|array|FunctionInterface|SelectQueryInterface ...$columns): static
    {
        foreach ($columns as $column) {
            if (is_string($column)) {
                $this->columns[] = [$this->getTable(), $column, null];
                continue;
            }

            if ($column instanceof FunctionInterface ||
                $column instanceof SelectQueryInterface
            ) {
                $this->columns[] = ['@', $column, null];
                continue;
            }

            if (!is_array($column)) {
                throw new InvalidArgumentException();
            }

            $column = array_values($column);

            $count = count($column);

            switch ($count) {
                case 2:
                    if ($column[0] instanceof FunctionInterface ||
                        $column[0] instanceof SelectQueryInterface
                    ) {
                        $column = ['@', $column[0], $column[1]];
                    } else {
                        $column = [$column[0], $column[1], null];
                    }
                    break;
                case 3:
                    break;
                default:
                    throw new InvalidArgumentException();
            }

            if ($column[0] !== '@' &&
                (
                    $column[1] instanceof FunctionInterface ||
                    $column[1] instanceof SelectQueryInterface
                )
            ) {
                throw new InvalidArgumentException();
            }

            // Scalars must have an 'AS'
            if ($column[0] === null && $column[2] === null) {
                throw new InvalidArgumentException();
            }

            $this->columns[] = [$column[0], $column[1], $column[2]];
        }

        return $this;
    }
}
