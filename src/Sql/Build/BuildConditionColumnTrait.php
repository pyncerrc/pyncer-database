<?php
namespace Pyncer\Database\Sql\Build;

use Pyncer\Database\Function\FunctionInterface;

trait BuildConditionColumnTrait
{
    private function buildConditionColumn(mixed $column): string
    {
        if (is_array($column)) {
            if ($column[0] == '@') {
                if ($column[1] instanceof FunctionInterface) {
                    $column = $column[1]->getQueryString();
                } else {
                    $column = $this->buildColumn($column[1]);
                }
            } elseif ($column[0] === null) {
                $column = $this->buildScalar($column[1]);
            } else {
                $column = $this->buildTable($column[0]) .
                    '.' .
                    $this->buildColumn($column[1]);
            }
        } elseif ($column instanceof FunctionInterface) {
            $column = $column->getQueryString();
        } else {
            $column = $this->buildTable($this->getTable()) .
                '.' .
                $this->buildColumn($column);
        }

        return $column;
    }
}
