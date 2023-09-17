<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\AbstractUpdateQuery;
use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Record\Conditions;
use Pyncer\Exception\UnexpectedValueException;

use function implode;

class UpdateQuery extends AbstractUpdateQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    protected function initializeWhere(): ConditionsInterface
    {
        return new Conditions($this);
    }

    public function getQueryString(): string
    {
        if (!$this->values) {
            throw new UnexpectedValueException('Expected columns.');
        }

        $query = 'UPDATE ' . $this->buildTable($this->getTable()) . ' SET ';

        $columns = [];

        foreach ($this->values as $column => $value) {
            if ($value instanceof FunctionInterface) {
                $value = $value->getQueryString();
            } else {
                $value = $this->buildScalar($value);
            }

            $columns[] =
                $this->buildTable($this->getTable()) .
                '.' . $this->buildColumn($column) . ' = ' . $value;
        }

        $query .= implode(',', $columns);

        $condition = $this->getWhere()->getQueryString();
        if ($condition) {
            $query .= ' WHERE ' . $condition;
        }

        return $query;
    }
}
