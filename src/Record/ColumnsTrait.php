<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Exception\InvalidArgumentException;

trait ColumnsTrait
{
    protected array $columns = [];

    public function columns(string|array ...$columns): static
    {
        foreach ($columns as $column) {
            if (is_string($column)) {
                $this->columns[] = [$this->getTable(), $column, null];
                continue;
            }

            if (!is_array($column)) {
                throw new InvalidArgumentException();
            }

            $column = array_values($column);

            $count = count($column);
            switch ($count) {
                case 2:
                    // Strings and Aliases must have an 'AS'
                    if ($column[0] === null || $column[0] === '@') {
                        throw new InvalidArgumentException();
                    }

                    if ($column[0] instanceof FunctionInterface ||
                        $column[0] instanceof SelectQueryInterface
                    ) {
                        $this->columns[] = ['@', $column[0], $column[1]];
                    } else {
                        $this->columns[] = [$column[0], $column[1], null];
                    }
                    break;
                case 3:
                    if ($column[0] !== '@' &&
                        (
                            $column[1] instanceof FunctionInterface ||
                            $column[1] instanceof SelectQueryInterface
                        )
                    ) {
                        throw new InvalidArgumentException();
                    }

                    $this->columns[] = [$column[0], $column[1], $column[2]];
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        }

        return $this;
    }
}
