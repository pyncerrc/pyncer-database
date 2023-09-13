<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\OrderByQueryInterface;
use Pyncer\Exception\InvalidArgumentException;

trait GroupByTrait
{
    protected ?array $groupBys = null;

    public function groupBy(string|array ...$columns): static
    {
        $this->groupBys = [];

        foreach ($columns as $column) {
            if (is_string($column)) {
                $this->groupBys[] = [$this->getTable(), $column];
                continue;
            }

            if (!is_array($column)) {
                throw new InvalidArgumentException();
            }

            $column = array_values($column);

            if (count($column) === 2) {
                $this->groupBys[] = [$column[0], $column[1]];
            } else {
                throw new InvalidArgumentException();
            }
        }

        // Automatically optimize order by for group by
        if ($this instanceof OrderByQueryInterface &&
            !$this->hasOrderBy()
        ) {
            $this->orderBy(null);
        }

        return $this;
    }

    public function hasGroupBy(): bool
    {
        if ($this->groupBys === null) {
            return false;
        }

        return (count($this->groupBys) > 0);
    }
}
