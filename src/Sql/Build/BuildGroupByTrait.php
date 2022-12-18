<?php
namespace Pyncer\Database\Sql\Build;

trait BuildGroupByTrait
{
    private function buildGroupBy(array $groupBys): string
    {
        $query = " GROUP BY ";

        $groupByQueries = [];

        foreach ($groupBys as $value) {
            $groupByQueries[] = $this->buildTable($value[0]) .
                '.' .
                $this->buildColumn($value[1]);
        }

        $query .= implode(',', $groupByQueries);

        return $query;
    }
}
