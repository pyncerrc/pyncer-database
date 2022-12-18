<?php
namespace Pyncer\Database\Sql\Build;

use Pyncer\Database\Function\FunctionInterface;

trait BuildColumnsTrait
{
    private function buildColumns(array $columns): string
    {
        if (!$columns) {
            return ' ' . $this->buildTable($this->getTable()) . ".*";
        }

        $columns = [];

        foreach ($this->columns as $value) {
            if ($value[0] === '@') {
                if ($value[1] instanceof FunctionInterface) {
                    $column = $value[1]->getQueryString();
                } else {
                    $column = $this->buildColumn($value[1]);
                }
            } elseif ($value[0] === null) {
                // If table is null, specific string is being inserted into selected columns
                $column = $this->buildScalar($value[1]);
            } elseif ($value[1] === '*') {
                if ($value[2] !== null) {
                    $result = $this->getConnection()->execute(sprintf(
                        "SELECT `COLUMN_NAME`
                        FROM `INFORMATION_SCHEMA`.`COLUMNS`
                        WHERE `TABLE_SCHEMA`=%s AND `TABLE_NAME`=%s",
                        $this->buildScalar(
                            $this->getConnection()->getDatabase()
                        ),
                        $this->buildScalar(
                            $this->buildTable($value[0], true)
                        )
                    ));

                    while ($row = $this->getConnection()->fetch($result)) {
                        $column = $this->buildTable($value[0]) . "." .
                            $this->buildColumn($row['COLUMN_NAME']) . " AS " .
                            $this->buildColumn($value[2] . $row['COLUMN_NAME']);

                        $columns[] = $column;
                    }

                    continue;
                } else {
                    $column = $this->buildTable($value[0]) . ".*";
                }
            } else {
                $column = $this->buildTable($value[0]) .
                    "." .
                    $this->buildColumn($value[1]);;
            }

            if ($value[2] !== null) {
                $column .= " AS " . $this->buildColumn($value[2]);
            }

            $columns[] = $column;
        }

        return ' ' . implode(",", $columns);
    }
}
