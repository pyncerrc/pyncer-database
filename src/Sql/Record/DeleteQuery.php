<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\Record\AbstractDeleteQuery;
use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildJoinsTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Exception\UnexpectedValueException;

use function count;
use function implode;

class DeleteQuery extends AbstractDeleteQuery
{
    use BuildColumnTrait;
    use BuildJoinsTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    protected function initializeWhere(): ConditionsInterface
    {
        return new Conditions($this);
    }

    public function getQueryString(): string
    {
        $query = 'DELETE' ;
        if ($this->joins) {
            $tables = [];

            foreach ($this->getTables() as $table) {
                $tables[] = $this->buildTable($table);
            }

            $query .= ' ' . implode(', ', $tables);

            $query .= ' FROM ' . $this->buildTable($this->getTable());

            $query .= ' ' . $this->buildJoins($this->joins);
        } else {
            if (count($this->getTables()) > 1) {
                throw new UnexpectedValueException('Multiple tables with no joins.');
            }

            $query .= ' FROM ' . $this->buildTable($this->getTable());
        }

        $condition = $this->getWhere()->getQueryString();
        if ($condition) {
            $query .= ' WHERE ' . $condition;
        }

        return $query;
    }
}
