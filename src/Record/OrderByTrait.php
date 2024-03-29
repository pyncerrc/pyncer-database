<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Exception\InvalidArgumentException;

trait OrderByTrait
{
    protected ?array $orderBys = null;

    public function orderBy(null|string|array|FunctionInterface ...$columns): static
    {
        $this->orderBys = [];
        $hasNull = false;

        foreach ($columns as $column) {
            if (is_null($column)) {
                $hasNull = true;
                $this->orderBys[] = null;
                continue;
            }

            if (is_string($column)) {
                $this->orderBys[] = [$this->getTable(), $column, 'asc'];
                continue;
            }

            if ($column instanceof FunctionInterface) {
                $this->orderBys[] = ['@', $column, 'asc'];
                continue;
            }

            if (!is_array($column)) {
                throw new InvalidArgumentException();
            }

            $column = array_values($column);

            $count = count($column);
            switch ($count) {
                case 2:
                    if ($column[0] === null || $column[0] === '@') {
                        $column = [$column[0], $column[1], 'asc'];
                    } elseif ($column[0] instanceof FunctionInterface) {
                        $column = ['@', $column[0], $column[1]];
                    } elseif (in_array($column[1], ['asc', 'desc', '>', '<'], true)) {
                        $column = [$this->getTable(), $column[0], $column[1]];
                    } else {
                        $column = [$column[0], $column[1], 'asc'];
                    }
                    break;
                case 3:
                    break;
                default:
                    throw new InvalidArgumentException();
            }

            if ($column[2] === '>') {
                $column[2] = 'asc';
            } elseif ($column[2] === '<') {
                $column[2] = 'desc';
            }

            if ($column[0] !== '@' &&
                $column[1] instanceof FunctionInterface
            ) {
                throw new InvalidArgumentException();
            }

            if (!in_array($column[2], ['asc', 'desc'], true)) {
                throw new InvalidArgumentException();
            }

            $this->orderBys[] = [$column[0], $column[1], $column[2]];
        }

        if ($hasNull && count($this->orderBys) > 1) {
            throw new InvalidArgumentException();
        }

        return $this;
    }

    public function hasOrderBy(): bool
    {
        if ($this->orderBys === null) {
            return false;
        }

        return (count($this->orderBys) > 0);
    }
}
