<?php
namespace Pyncer\Database\Sql\Build;

use Pyncer\Database\Function\FunctionInterface;

trait BuildOrderByTrait
{
    private function buildOrderBy(array $orderBys): string
    {
        $query = " ORDER BY ";

        $orderByQueries = [];

        foreach ($orderBys as $value) {
            if ($value === null) {
                $orderByQueries[] = 'NULL';
            } elseif ($value[0] === '@') {
                if ($value[1] instanceof FunctionInterface) {
                    $orderByQueries[] = $value[1]->getQueryString() .
                        ' ' . strtoupper($value[2]);
                } else {
                    $orderByQueries[] = $this->buildColumn($value[1]) .
                        ' ' . strtoupper($value[2]);
                }
            } elseif ($value[0] === null) {
                $orderByQueries[] = $this->buildColumn($value[1]) .
                    ' ' . strtoupper($value[2]);
            } else{
                $orderByQueries[] = $this->buildTable($value[0]) .
                    '.' .
                    $this->buildColumn($value[1]) .
                    ' ' . strtoupper($value[2]);
            }
        }

        $query .= implode(',', $orderByQueries);

        return $query;
    }
}
