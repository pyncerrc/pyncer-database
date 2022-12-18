<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\Record\AbstractSelectQuery;
use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildColumnsTrait;
use Pyncer\Database\Sql\Build\BuildGroupByTrait;
use Pyncer\Database\Sql\Build\BuildJoinsTrait;
use Pyncer\Database\Sql\Build\BuildOrderByTrait;
use Pyncer\Database\Sql\Record\Conditions;

class SelectQuery extends AbstractSelectQuery
{
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildColumnsTrait;
    use BuildGroupByTrait;
    use BuildJoinsTrait;
    use BuildOrderByTrait;

    protected function initializeWhere(): ConditionsInterface
    {
        return new Conditions($this);
    }

    protected function initializeHaving(): ConditionsInterface
    {
        return new Conditions($this);
    }

    public function getQueryString(): string
    {
        $query = "SELECT" . ($this->distinct ? " DISTINCT" : '');

        // Columns
        $query .= $this->buildColumns($this->columns);

        // Tables
        $query .= " FROM " . $this->buildTable($this->getTable());

        // Joins
        if ($this->joins) {
            $query .= $this->buildJoins($this->joins);
        }

        // Where condition
        $condition = $this->getWhere()->getQueryString();
        if ($condition) {
            $query .= " WHERE " . $condition;
        }

        if ($this->groupBys) {
            $query .= $this->buildGroupBy($this->groupBys);
        }

        // Having condition
        $condition = $this->getHaving()->getQueryString();
        if ($condition) {
            $query .= " HAVING " . $condition;
        }

        if ($this->orderBys) {
            $query .= $this->buildOrderBy($this->orderBys);
        }

        if ($this->limit) {
            $query .= " LIMIT " . $this->limit[1] . ", " . $this->limit[0];
        }

        return $query;
    }
}
