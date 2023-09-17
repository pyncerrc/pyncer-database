<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\AbstractInsertQuery;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;

use function implode;

class InsertQuery extends AbstractInsertQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    public function getQueryString(): string
    {
        $query = 'INSERT' . ($this->ignore ? ' IGNORE' : '') . ' INTO ' .
            $this->buildTable($this->getTable());

        $columns = [];
        $values = [];

        if ($this->values) {
            foreach ($this->values as $column => $value) {
                $columns[] = $this->buildColumn($column);

                if ($value instanceof FunctionInterface) {
                    $value = $value->getQueryString();
                } else {
                    $value = $this->buildScalar($value);
                }

                $values[] = $value;
            }
        }

        $query .= ' (' . implode(',', $columns) . ') VALUES(' . implode(',', $values) . ')';

        return $query;
    }
}
