<?php
namespace Pyncer\Database\Sql\Build;

trait BuildScalarTrait
{
    private function buildScalar(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return ($value ? "'1'" : "'0'");
        }

        return "'" . $this->getConnection()->escapeString($value) . "'";
    }
}
