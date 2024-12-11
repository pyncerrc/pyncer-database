<?php
namespace Pyncer\Database\Sql\Build;

use Pyncer\Exception\InvalidArgumentException;

trait BuildColumnTrait
{
    protected function buildColumn(string $column): string
    {
        if ($column === '') {
            throw new InvalidArgumentException('Column cannot be an empty string.');
        }

        $column = $this->getConnection()->escapeName($column);

        return "`" . $column . "`";
    }
}
