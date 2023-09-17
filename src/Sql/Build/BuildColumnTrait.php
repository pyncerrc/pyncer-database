<?php
namespace Pyncer\Database\Sql\Build;

trait BuildColumnTrait
{
    protected function buildColumn(string $column, bool $asValue = false): string
    {
        if ($asValue) {
            return $column;
        }

        $column = $this->getConnection()->escapeName($column);

        return "`" . $column . "`";
    }
}
