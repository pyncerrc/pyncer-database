<?php
namespace Pyncer\Database\Sql\Build;

use DateTime;

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

        if ($value instanceof DateTime) {
            $value = $this->getConnection()->dateTime($value);
        }

        return "'" . $this->getConnection()->escapeString($value) . "'";
    }
}
