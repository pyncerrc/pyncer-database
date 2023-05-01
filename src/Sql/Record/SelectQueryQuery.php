<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Record\AbstractSelectQuery;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildColumnsTrait;
use Pyncer\Database\Sql\Build\BuildGroupByTrait;
use Pyncer\Database\Sql\Build\BuildJoinsTrait;
use Pyncer\Database\Sql\Build\BuildOrderByTrait;
use Pyncer\Database\Sql\Record\Conditions;

class SelectQueryQuery extends AbstractSelectQuery
{
    use BuildTableTrait;
    use BuildColumnsTrait;
    use BuildGroupByTrait;
    use BuildJoinsTrait;
    use BuildOrderByTrait;

    private SelectQueryInterface $query;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        SelectQueryInterface $query
    )
    {
        parent::__construct($connection, $table);

        $this->setQuery($query);
    }

    public function getQuery(): SelectQueryInterface
    {
        return $this->query;
    }
    protected function setQuery(SelectQueryInterface $value): static
    {
        $this->query = $value;
        return $this;
    }

    protected function initializeWhere(): ConditionsInterface
    {
        return new Condition($this);
    }

    protected function initializeHaving(): ConditionsInterface
    {
        return new Condition($this);
    }

    public function getQueryString(): string
    {
        $query = 'SELECT' . ($this->distinct ? ' DISTINCT' : '');

        // Columns
        $query .= $this->buildColumns($this->columns);

        // Tables
        $query .= ' FROM (' . $this->getQuery()->getQueryString() . ') AS ' .
            $this->buildTable($this->getTable());

        // Joins
        if ($this->joins) {
            $query .= ' ' . $this->buildJoins($this->joins);
        }

        // Where condition
        $condition = $this->getWhere()->getQueryString();
        if ($condition) {
            $query .= ' WHERE ' . $condition;
        }

        if ($this->groupBys) {
            $query .= $this->buildGroupBy($this->groupBys);
        }

        // Having condition
        $condition = $this->getHaving()->getQueryString();
        if ($condition) {
            $query .= ' HAVING ' . $condition;
        }

        if ($this->orderBys) {
            $query .= $this->buildOrderBy($this->orderBys);
        }

        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit[1] . ', ' . $this->limit[0];
        }

        return $query;
    }
}
